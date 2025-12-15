<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Reminder;
use Carbon\Carbon;

class ReminderNotificationCount extends Component
{
    public $count = 0;
    public $activeReminders = [];
    public $imminentReminders = [];  // Rappels dans les 10 prochaines minutes
    public $arrivedReminders = [];   // Rappels déjà arrivés
    public $previousCount = 0;
    public $shouldPlaySound = false;

    protected $listeners = [
        'reminderCreated' => 'refreshCount',
        'reminderUpdated' => 'refreshCount',
        'reminderDeleted' => 'refreshCount',
        'reminderMarkedAsRead' => 'refreshCount'
    ];

    public function mount()
    {
        $this->updateCount();
    }

    public function render()
    {
        return view('livewire.reminder-notification-count');
    }

    /**
     * Met à jour le compteur et récupère les rappels actifs non lus
     */
    public function updateCount()
    {
        $now = Carbon::now();
        $tenMinutesLater = $now->copy()->addMinutes(10);

        // =====================================================
        // 1. RAPPELS IMMINENTS (dans les 10 prochaines minutes)
        // =====================================================
        $this->imminentReminders = Reminder::with(['document', 'folder'])
            ->where('user_id', auth()->id())
            ->where('is_active', true)
            ->where('is_completed', false)
            ->where('is_notified', false)
            ->where(function ($query) use ($now, $tenMinutesLater) {
                // Rappels d'aujourd'hui qui arrivent dans les 10 prochaines minutes
                $query->where(function ($q) use ($now, $tenMinutesLater) {
                    $q->where('reminder_date', $now->toDateString())
                      ->where('reminder_time', '>', $now->toTimeString())
                      ->where('reminder_time', '<=', $tenMinutesLater->toTimeString());
                })
                // OU rappels de demain si on est proche de minuit
                ->orWhere(function ($q) use ($now, $tenMinutesLater) {
                    if ($tenMinutesLater->toDateString() !== $now->toDateString()) {
                        $q->where('reminder_date', $tenMinutesLater->toDateString())
                          ->where('reminder_time', '<=', $tenMinutesLater->toTimeString());
                    }
                });
            })
            ->orderBy('reminder_date', 'asc')
            ->orderBy('reminder_time', 'asc')
            ->get();

        // =====================================================
        // 2. RAPPELS ARRIVÉS (date/heure passée, non lus)
        // =====================================================
        $this->arrivedReminders = Reminder::with(['document', 'folder'])
            ->where('user_id', auth()->id())
            ->where('is_active', true)
            ->where('is_completed', false)
            ->where('is_notified', false)
            ->where(function ($query) use ($now) {
                // Rappels dont la date est passée
                $query->where('reminder_date', '<', $now->toDateString())
                    // OU rappels d'aujourd'hui dont l'heure est passée
                    ->orWhere(function ($q) use ($now) {
                        $q->where('reminder_date', $now->toDateString())
                          ->where('reminder_time', '<=', $now->toTimeString());
                    });
            })
            ->orderBy('reminder_date', 'desc')
            ->orderBy('reminder_time', 'desc')
            ->get();

        // Fusionner les deux collections (arrivés en premier, puis imminents)
        $this->activeReminders = $this->arrivedReminders->merge($this->imminentReminders);

        $newCount = $this->activeReminders->count();

        // Détecter si de nouveaux rappels sont arrivés pour jouer le son
        if ($newCount > $this->previousCount) {
            $this->shouldPlaySound = true;
            $this->dispatch('playReminderSound');
        } else {
            $this->shouldPlaySound = false;
        }

        $this->previousCount = $this->count;
        $this->count = $newCount;
    }

    /**
     * Marquer un rappel spécifique comme lu
     */
    public function markAsRead($reminderId)
    {
        $reminder = Reminder::where('user_id', auth()->id())
            ->where('id', $reminderId)
            ->first();

        if ($reminder) {
            $reminder->update([
                'is_notified' => true,
                'notified_at' => Carbon::now()
            ]);

            $this->updateCount();
            $this->dispatch('reminderMarkedAsRead');
        }
    }

    /**
     * Marquer tous les rappels comme lus
     */
    public function markAllAsRead()
    {
        $now = Carbon::now();
        $tenMinutesLater = $now->copy()->addMinutes(10);

        Reminder::where('user_id', auth()->id())
            ->where('is_active', true)
            ->where('is_completed', false)
            ->where('is_notified', false)
            ->where(function ($query) use ($now, $tenMinutesLater) {
                // Rappels arrivés
                $query->where(function ($q) use ($now) {
                    $q->where('reminder_date', '<', $now->toDateString())
                      ->orWhere(function ($subQ) use ($now) {
                          $subQ->where('reminder_date', $now->toDateString())
                               ->where('reminder_time', '<=', $now->toTimeString());
                      });
                })
                // OU rappels imminents (dans les 10 min)
                ->orWhere(function ($q) use ($now, $tenMinutesLater) {
                    $q->where('reminder_date', $now->toDateString())
                      ->where('reminder_time', '>', $now->toTimeString())
                      ->where('reminder_time', '<=', $tenMinutesLater->toTimeString());
                });
            })
            ->update([
                'is_notified' => true,
                'notified_at' => Carbon::now()
            ]);

        $this->updateCount();
        $this->dispatch('reminderMarkedAsRead');
    }

    /**
     * Rafraîchir le compteur
     */
    public function refreshCount()
    {
        $this->updateCount();
    }

    /**
     * Polling automatique pour mettre à jour les notifications
     */
    public function poll()
    {
        $this->updateCount();
    }

    /**
     * Vérifier si un rappel est imminent (dans les 10 prochaines minutes)
     */
    public function isImminent($reminder)
    {
        return $this->imminentReminders->contains('id', $reminder->id);
    }

    /**
     * Vérifier si un rappel est arrivé (date/heure passée)
     */
    public function isArrived($reminder)
    {
        return $this->arrivedReminders->contains('id', $reminder->id);
    }
}
