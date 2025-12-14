<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Reminder;
use Livewire\WithPagination;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReminderNotification;

class ReminderList extends Component
{
    use WithPagination;

    public $search = '';
    public $filterType = 'all'; // all, completed, active, overdue
    public $perPage = 10;
    public $isEditing = false;
    public $sentReminders = [];

    protected $queryString = ['search', 'filterType'];

    public function render()
    {
        $this->checkAndSendReminders();
        $now = Carbon::now();
        $nowDate = $now->toDateString();
        $nowTime = $now->toTimeString();

        $query = Reminder::with(['document', 'folder', 'user'])
            ->where('user_id', auth()->id());

        // Filtrer par recherche
        if ($this->search) {
            $query->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('message', 'like', '%' . $this->search . '%');
        }

        // Filtrer par statut
        switch ($this->filterType) {
            case 'completed':
                $query->where('is_completed', true);
                break;
            case 'active':
                $query->where('is_active', true)
                      ->where('is_completed', false);
                break;
            case 'overdue':
                $now = Carbon::now();
                $query->where('is_completed', false)
                      ->where(function ($q) use ($now) {
                          $q->where('reminder_date', '<', $now->toDateString())
                            ->orWhere(function ($subq) use ($now) {
                                $subq->where('reminder_date', $now->toDateString())
                                     ->where('reminder_time', '<', $now->toTimeString());
                            });
                      });
                break;
        }

        // Trier les rappels selon les priorités définies par l'utilisateur:
        // Priorité 1 : "Arrivé" - Les rappels en retard (passés)
        // Priorité 2 : "Moins de 10min avant arrivé du rappel" - Les rappels qui arriveront dans les 10 prochaines minutes
        // Priorité 3 : "Pas encore arrivé" - Les rappels futurs
        // Priorité 4 : "Déjà passé" - Les rappels complétés
        // $now = Carbon::now();
        // $tenMinutesLater = (clone $now)->addMinutes(10); // Cloner $now pour éviter de le modifier

        /* =========================================================
         | TRI pour agencé les larappel des plus iminent au moin iminent
         ========================================================= */

        $query->selectRaw("
            reminders.*,
            CASE
                -- 1️⃣ ARRIVÉ (retard < 10 min)
                WHEN is_completed = 0
                    AND TIMESTAMPDIFF(
                        MINUTE,
                        CONCAT(reminder_date, ' ', reminder_time),
                        ?
                    ) BETWEEN 0 AND 10
                THEN 1

                -- 2️⃣ IMMINENT (dans 10 min)
                WHEN is_completed = 0
                    AND TIMESTAMPDIFF(
                        MINUTE,
                        ?,
                        CONCAT(reminder_date, ' ', reminder_time)
                    ) BETWEEN 0 AND 10
                THEN 2

                -- 3️⃣ FUTUR (>10 min)
                WHEN is_completed = 0
                    AND TIMESTAMPDIFF(
                        MINUTE,
                        ?,
                        CONCAT(reminder_date, ' ', reminder_time)
                    ) > 10
                THEN 3

                -- 4️⃣ DÉJÀ PASSÉ (>10 min)
                WHEN is_completed = 0
                    AND TIMESTAMPDIFF(
                        MINUTE,
                        CONCAT(reminder_date, ' ', reminder_time),
                        ?
                    ) > 10
                THEN 4

                -- 5️⃣ COMPLÉTÉ
                WHEN is_completed = 1 THEN 5

                ELSE 6
            END AS time_priority
        ", [
            $now, // arrivé
            $now, // imminent
            $now, // futur
            $now  // déjà passé
        ])

        ->orderBy('time_priority')

        ->orderByRaw("
            ABS(
                TIMESTAMPDIFF(
                    SECOND,
                    CONCAT(reminder_date, ' ', reminder_time),
                    ?
                )
            ) ASC
        ", [$now]);


        // Récupérer les rappels paginés avec les relations nécessaires
        $reminders = $query->paginate($this->perPage);

        // Pour chaque rappel, charger les dossiers parents de manière récursive
        foreach($reminders as $reminder) {
            if ($reminder->folder_id && $reminder->folder) {
                $this->loadFolderParents($reminder->folder);
            } elseif ($reminder->document && $reminder->document->folder) {
                $this->loadFolderParents($reminder->document->folder);
            }
        }

        return view('livewire.reminder-list', [
            'reminders' => $reminders
        ]);
    }

    private function checkAndSendReminders()
    {
        $now = Carbon::now();

        // Récupérer les rappels non complétés qui n'ont pas encore été notifiés
        // On utilise la méthode selectRaw pour filtrer directement dans la requête SQL
        // pour optimiser les performances
        $remindersToCheck = Reminder::with(['user'])
            ->where('is_completed', false)
            ->whereNotNull('reminder_date')
            ->whereNotNull('reminder_time')
            ->whereNull('email_sent_at') // N'envoyer l'email que si ce n'a pas déjà été fait
            ->get();

        foreach ($remindersToCheck as $reminder) {
            try {
                // Vérifier que les dates et heures sont valides
                if (!$reminder->reminder_date || !$reminder->reminder_time) {
                    continue; // Passer au rappel suivant s'il n'y a pas de date ou d'heure
                }

                // Gestion correcte des dates/temps qui pourraient être des objets Carbon ou des chaînes
                $date = $reminder->reminder_date;
                $time = $reminder->reminder_time;

                // Vérifier et formater correctement la date et l'heure
                if ($date instanceof Carbon) {
                    $date = $date->format('Y-m-d');
                }
                if ($time instanceof Carbon) {
                    $time = $time->format('H:i:s');
                }

                // S'assurer que les formats sont corrects
                $date = trim($date);
                $time = trim($time);

                // Créer la date et l'heure du rappel
                $reminderDateTime = Carbon::parse("{$date} {$time}");

                // Calculer la différence en minutes entre maintenant et le rappel
                // Si diffInMinutes est positif, alors le rappel est dans le futur
                // Si diffInMinutes est négatif, alors le rappel est dans le passé
                $diffInMinutes = $now->diffInMinutes($reminderDateTime, false);

                // Envoyer un email 10 minutes avant l'heure du rappel
                // Cela signifie que quand il reste environ 10 minutes avant le rappel, diffInMinutes ≈ 10
                if ($diffInMinutes <= 10 && $diffInMinutes > 0) {
                    if ($reminder->user && $reminder->user->email) {
                        Mail::to($reminder->user->email)->send(new ReminderNotification($reminder));

                        // Marquer le rappel comme ayant été notifié pour éviter les emails en double
                        $reminder->update(['email_sent_at' => now()]);

                        // Optionnel: Ajouter un log pour le suivi
                        \Log::info("Email envoyé pour le rappel ID: {$reminder->id} à l'utilisateur: {$reminder->user->email}");
                    }
                }
            } catch (\Exception $e) {
                // En cas d'erreur, enregistrer l'erreur et continuer avec le prochain rappel
                \Log::error("Erreur lors de l'envoi de l'email pour le rappel ID: {$reminder->id}", [
                    'error' => $e->getMessage()
                ]);
                continue;
            }
        }
    }

    public function updateReminders()
    {
        $this->dispatch('$refresh');
    }

    private function loadFolderParents($folder)
    {
        $current = $folder;
        while ($current && $current->parent_id) {
            $current = $current->parent;
        }
    }

    public function toggleCompleted($id)
    {
        $reminder = Reminder::where('user_id', auth()->id())->findOrFail($id);
        $reminder->update([
            'is_completed' => !$reminder->is_completed,
            'completed_at' => $reminder->is_completed ? null : Carbon::now()
        ]);

        // Émettre un événement pour notifier les autres composants
        $this->dispatch('reminderUpdated');
    }

    public function deleteReminder($id)
    {
        $reminder = Reminder::where('user_id', auth()->id())->findOrFail($id);
        $reminder->delete();

        // Émettre un événement pour notifier les autres composants
        $this->dispatch('reminderDeleted');
    }

    public $editingReminderId = null;
    public $editingReminderTitle = '';
    public $editingReminderMessage = '';
    public $editingReminderDate = '';
    public $editingReminderTime = '';
    public $editingReminderFileId = null;
    public $editingReminderFolderId = null;

    protected $listeners = [
        'close-edit-modal' => 'closeEditModal'
    ];

    public function editReminder($id)
    {
        $reminder = Reminder::with(['document', 'folder', 'user'])->where('user_id', auth()->id())->findOrFail($id);

        // Remplir les propriétés avec les données du rappel
        $this->editingReminderId = $reminder->id;
        $this->editingReminderTitle = $reminder->title;
        $this->editingReminderMessage = $reminder->message;
        $this->editingReminderDate = $reminder->reminder_date ? $reminder->reminder_date->format('Y-m-d') : null;
        $this->editingReminderTime = $reminder->reminder_time ? $reminder->reminder_time : null;
        $this->editingReminderFileId = $reminder->file_id;
        $this->editingReminderFolderId = $reminder->folder_id;

        // Mettre à jour l'état d'édition
        $this->isEditing = true;

        // Émettre un événement pour ouvrir le modal
        $this->dispatch('editReminderRequested', [
            'reminder' => [
                'id' => $reminder->id,
                'title' => $reminder->title,
                'message' => $reminder->message,
                'reminder_date' => $reminder->reminder_date ? $reminder->reminder_date->format('Y-m-d') : null,
                'reminder_time' => $reminder->reminder_time ? $reminder->reminder_time : null,
                'file_id' => $reminder->file_id,
                'folder_id' => $reminder->folder_id,
                'is_active' => $reminder->is_active,
                'is_completed' => $reminder->is_completed,
            ]
        ]);
    }

    public function updateReminder()
    {
        $validatedData = $this->validate([
            'editingReminderTitle' => 'required|string|max:255',
            'editingReminderMessage' => 'nullable|string',
            'editingReminderDate' => 'required|date',
            'editingReminderTime' => 'required',
        ]);

        $reminder = Reminder::where('user_id', auth()->id())->findOrFail($this->editingReminderId);
        $reminder->update([
            'title' => $this->editingReminderTitle,
            'message' => $this->editingReminderMessage,
            'reminder_date' => $this->editingReminderDate,
            'reminder_time' => $this->editingReminderTime,
        ]);

        // Émettre un événement pour notifier de la mise à jour
        $this->dispatch('reminderUpdated');

        // Définir un message de succès
        session()->flash('message', 'rappel modifier');

        // Réinitialiser les propriétés d'édition
        $this->reset(['editingReminderId', 'editingReminderTitle', 'editingReminderMessage', 'editingReminderDate', 'editingReminderTime']);

        // Émettre un événement pour fermer le modal
        $this->dispatch('reminder-updated-and-closed');
    }

    public function closeEditModal()
    {
        // Réinitialiser les propriétés d'édition
        $this->reset(['editingReminderId', 'editingReminderTitle', 'editingReminderMessage', 'editingReminderDate', 'editingReminderTime']);
        $this->isEditing = false;
    }


    public function getFolderPath($folder)
    {
        if (!$folder) {
            return 'Dossier supprimé';
        }

        // Utiliser l'attribut d'accessibilité du modèle Folder
        return $folder->path;
    }

    public function getDocumentPath($document)
    {
        if (!$document) {
            return 'Document supprimé';
        }

        if (!$document->folder) {
            return $document->nom . ' (dossier racine)';
        }

        $folderPath = $document->folder->path ?? $document->folder->name;

        return $folderPath . ' / ' . $document->nom;
    }
}