<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Reminder;
use Carbon\Carbon;

class ReminderNotificationCount extends Component
{
    public $count = 0;

    protected $listeners = [
        'reminderCreated' => 'refreshCount',
        'reminderUpdated' => 'refreshCount',
        'reminderDeleted' => 'refreshCount'
    ];

    public function mount()
    {
        $this->updateCount();
    }

    public function render()
    {
        return view('livewire.reminder-notification-count');
    }

    public function updateCount()
    {
        $now = Carbon::now();
        $tenMinutesLater = $now->copy()->addMinutes(10);

        $newCount = Reminder::where('user_id', auth()->id())
            ->where('is_active', true)
            ->where('is_completed', false)
            ->where(function ($query) use ($now, $tenMinutesLater) {
                $query->where('reminder_date', $now->toDateString())
                      ->whereBetween('reminder_time', [$now->toTimeString(), $tenMinutesLater->toTimeString()]);
            })
            ->orWhere(function ($query) use ($now, $tenMinutesLater) {
                $query->where('reminder_date', '>', $now->toDateString())
                      ->where('reminder_date', '<=', $tenMinutesLater->toDateString())
                      ->where('reminder_time', '<=', $tenMinutesLater->toTimeString());
            })
            ->count();

        if ($newCount !== $this->count) {
            $this->count = $newCount;
            // Forcer une mise à jour de l'interface utilisateur
            $this->dispatch('$refresh');
        }
    }

    public function refreshCount()
    {
        $this->updateCount();
    }
}