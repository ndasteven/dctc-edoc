<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Reminder;
use Livewire\WithPagination;
use Carbon\Carbon;

class ReminderList extends Component
{
    use WithPagination;

    public $search = '';
    public $filterType = 'all'; // all, completed, active, overdue
    public $perPage = 10;
    public $isEditing = false;

    protected $queryString = ['search', 'filterType'];

    public function render()
    {
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

        // Trier par statut (actif d'abord), puis par date/heure la plus proche
        // Les rappels terminés seront en bas, les plus proches en haut
        $query->orderBy('is_completed', 'asc')  // Actifs (0) d'abord, puis complétés (1)
              ->orderBy('reminder_date', 'asc')  // Puis tri par date la plus proche
              ->orderBy('reminder_time', 'asc'); // Puis par heure la plus proche

        // Récupérer les rappels paginés avec les relations nécessaires
        $reminders = $query->select(['*', 'is_completed as is_completed'])->paginate($this->perPage);

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
        $this->editingReminderTime = $reminder->reminder_time ? $reminder->reminder_time->format('H:i') : null;
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
                'reminder_time' => $reminder->reminder_time ? $reminder->reminder_time->format('H:i') : null,
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