<?php

namespace App\Models;

use App\Jobs\SendReminderEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class Reminder extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'message',
        'reminder_date',
        'reminder_time',
        'file_id',
        'folder_id',
        'user_id',
        'is_active',
        'is_completed',
        'is_notified',      // NOUVEAU
        'notified_at',      // NOUVEAU
        'email_sent_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'reminder_date' => 'date',
        'reminder_time' => 'string',
        'is_active' => 'boolean',
        'is_completed' => 'boolean',
        'is_notified' => 'boolean',     // NOUVEAU
        'notified_at' => 'datetime',    // NOUVEAU
        'email_sent_at' => 'datetime',
    ];

    /**
     * Get the attribute indicating if reminder is completed, ensuring it's correctly accessed
     */
    public function getIsCompletedAttribute()
    {
        return (bool) ($this->attributes['is_completed'] ?? false);
    }

    /**
     * Get the user that owns the reminder.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the document that the reminder is associated with (if any).
     */
    public function document()
    {
        return $this->belongsTo(Document::class, 'file_id');
    }

    /**
     * Get the folder that the reminder is associated with (if any).
     */
    public function folder()
    {
        return $this->belongsTo(Folder::class, 'folder_id');
    }

    /**
     * Scope to get active reminders only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get incomplete reminders only.
     */
    public function scopeIncomplete($query)
    {
        return $query->where('is_completed', false);
    }

    /**
     * Scope to get unnotified reminders only. (NOUVEAU)
     */
    public function scopeUnnotified($query)
    {
        return $query->where('is_notified', false);
    }

    /**
     * Scope to get reminders for a specific file.
     */
    public function scopeForFile($query, $fileId)
    {
        return $query->where('file_id', $fileId);
    }

    /**
     * Scope to get reminders for a specific folder.
     */
    public function scopeForFolder($query, $folderId)
    {
        return $query->where('folder_id', $folderId);
    }

    /**
     * Vérifie s'il existe déjà un rappel pour un fichier ou un dossier.
     */
    public static function hasReminder($fileId = null, $folderId = null)
    {
        $query = self::query();

        if ($fileId) {
            $query->where('file_id', $fileId);
        } elseif ($folderId) {
            $query->where('folder_id', $folderId);
        } else {
            return false;
        }

        return $query->exists();
    }

    /**
     * Obtient le rappel pour un fichier ou un dossier.
     */
    public static function getReminder($fileId = null, $folderId = null)
    {
        $query = self::query();

        if ($fileId) {
            $query->where('file_id', $fileId);
        } elseif ($folderId) {
            $query->where('folder_id', $folderId);
        } else {
            return null;
        }

        return $query->first();
    }

    /**
     * Scope to get future reminders.
     */
    public function scopeFuture($query)
    {
        return $query->where(function ($query) {
            $query->where('reminder_date', '>', now()->toDateString())
                ->orWhere(function ($query) {
                    $query->where('reminder_date', now()->toDateString())
                          ->where('reminder_time', '>', now()->toTimeString());
                });
        });
    }

    /**
     * Scope to get overdue reminders (NOUVEAU - amélioré).
     */
    public function scopeOverdue($query)
    {
        return $query->where(function ($query) {
            $query->where('reminder_date', '<', now()->toDateString())
                ->orWhere(function ($query) {
                    $query->where('reminder_date', now()->toDateString())
                          ->where('reminder_time', '<=', now()->toTimeString());
                });
        })
        ->where('is_completed', false);
    }

    /**
     * Scope pour les rappels à notifier (arrivés mais non lus) - NOUVEAU
     */
    public function scopePendingNotification($query)
    {
        $now = Carbon::now();

        return $query->where('is_active', true)
            ->where('is_completed', false)
            ->where('is_notified', false)
            ->where(function ($q) use ($now) {
                // Rappels dont la date est passée
                $q->where('reminder_date', '<', $now->toDateString())
                    // OU rappels d'aujourd'hui dont l'heure est passée
                    ->orWhere(function ($subQ) use ($now) {
                        $subQ->where('reminder_date', $now->toDateString())
                             ->where('reminder_time', '<=', $now->toTimeString());
                    });
            });
    }

    /**
     * Get time remaining until reminder
     */
    public function getTimeRemainingAttribute()
    {
        $reminderDate = $this->reminder_date instanceof \Carbon\Carbon ? $this->reminder_date->copy() : \Carbon\Carbon::parse($this->reminder_date);
        $reminderTime = $this->reminder_time instanceof \Carbon\Carbon ? $this->reminder_time->copy() : \Carbon\Carbon::parse($this->reminder_time);

        if ($reminderDate && $reminderTime) {
            try {
                $reminderDateTime = $reminderDate->setTime($reminderTime->hour, $reminderTime->minute, $reminderTime->second);
                $now = \Carbon\Carbon::now();
                $diffInSeconds = $now->diffInSeconds($reminderDateTime, false);

                if ($this->is_completed) {
                    return ['status' => 'Terminé', 'class' => 'text-gray-600'];
                } elseif ($diffInSeconds > 600) {
                    return ['status' => 'Pas encore arrivé', 'class' => 'text-green-600'];
                } elseif ($diffInSeconds > 0) {
                    return ['status' => 'moins de ' . ceil($diffInSeconds / 60) . ' min', 'class' => 'text-yellow-600 animate-pulse'];
                } elseif ($diffInSeconds >= -600) {
                    return ['status' => 'Arrivé', 'class' => 'text-red-600 font-bold'];
                } else {
                    return ['status' => 'déja passé', 'class' => 'text-red-800'];
                }
            } catch (\Exception $e) {
                // Gérer l'erreur si la date/heure est invalide
            }
        }
        return ['status' => 'Date invalide', 'class' => 'text-red-600'];
    }

    /**
     * Get the attribute to check if the reminder is imminent (less than 10 minutes away and not completed).
     */
    public function getIsImminentAttribute()
    {
        if ($this->is_completed || !$this->reminder_date || !$this->reminder_time) {
            return false;
        }

        $reminderDate = $this->reminder_date instanceof \Carbon\Carbon ? $this->reminder_date->copy() : \Carbon\Carbon::parse($this->reminder_date);
        $reminderTime = $this->reminder_time instanceof \Carbon\Carbon ? $this->reminder_time->copy() : \Carbon\Carbon::parse($this->reminder_time);

        $reminderDateTime = $reminderDate->setTime($reminderTime->hour, $reminderTime->minute, $reminderTime->second);
        $now = \Carbon\Carbon::now();

        $diffInMinutes = $now->diffInMinutes($reminderDateTime, false);

        return $diffInMinutes > 0 && $diffInMinutes <= 10;
    }

    /**
     * Get the formatted reminder datetime
     */
    public function getFormattedDatetimeAttribute()
    {
        if ($this->reminder_date && $this->reminder_time) {
            try {
                $dateValue = $this->reminder_date;
                $timeValue = $this->reminder_time;

                if (is_object($dateValue) && method_exists($dateValue, 'format')) {
                    $dateValue = $dateValue->format('Y-m-d');
                }

                if (is_object($timeValue) && method_exists($timeValue, 'format')) {
                    $timeValue = $timeValue->format('H:i:s');
                }

                $dateValue = (string)$dateValue;
                $timeValue = (string)$timeValue;

                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateValue) &&
                    preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $timeValue)) {

                    $combined = $dateValue . ' ' . $timeValue;
                    $datetime = \Carbon\Carbon::parse($combined);
                    return $datetime->format('d/m/Y H:i');
                }
            } catch (\Exception $e) {
                // Ne pas afficher l'erreur
            }
        }
        return 'Date invalide';
    }

    /**
     * Boot the model and attach event listeners
     */
    protected static function boot()
    {
        parent::boot();

        static::updating(function ($reminder) {
            if ($reminder->isDirty(['reminder_date', 'reminder_time'])) {
                $reminder->email_sent_at = null;
                // Réinitialiser is_notified si la date/heure change
                $reminder->is_notified = false;
                $reminder->notified_at = null;
            }
        });

        static::created(function ($reminder) {
            $reminder->scheduleReminderEmail();
        });

        static::updated(function ($reminder) {
            if ($reminder->isDirty(['reminder_date', 'reminder_time'])) {
                $reminder->rescheduleReminderEmail();
            }
        });
    }

    /**
     * Schedule a reminder email job to run at the appropriate time
     */
    public function scheduleReminderEmail()
    {
        if (!$this->is_active || $this->is_completed) {
            return;
        }

        if (!$this->reminder_date || !$this->reminder_time) {
            return;
        }

        $date = $this->reminder_date;
        $time = $this->reminder_time;

        if ($date instanceof Carbon) {
            $date = $date->format('Y-m-d');
        }

        if ($time instanceof Carbon) {
            $time = $time->format('H:i:s');
        } elseif (is_string($time) && strlen($time) === 5) {
            $time .= ':00';
        }

        $reminderDateTime = Carbon::parse("{$date} {$time}");
        $sendTime = $reminderDateTime->subMinutes(10);

        if ($sendTime->isFuture()) {
            SendReminderEmail::dispatch($this->id)->onQueue('default')->delay($sendTime);
        } else {
            SendReminderEmail::dispatch($this->id)->onQueue('default');
        }
    }

    /**
     * Reschedule a reminder email job when the reminder is updated
     */
    public function rescheduleReminderEmail()
    {
        $this->scheduleReminderEmail();
    }

    /**
     * Marquer le rappel comme lu/notifié - NOUVEAU
     */
    public function markAsNotified()
    {
        $this->update([
            'is_notified' => true,
            'notified_at' => Carbon::now()
        ]);
    }
}
