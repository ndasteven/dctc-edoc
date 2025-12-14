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
        'email_sent_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'reminder_date' => 'date',
        'reminder_time' => 'string',  // Utiliser string au lieu de datetime:H:i pour éviter les conversions indésirables
        'is_active' => 'boolean',
        'is_completed' => 'boolean',
        'email_sent_at' => 'datetime',
    ];

    /**
     * Get the attribute indicating if reminder is completed, ensuring it's correctly accessed
     */
    public function getIsCompletedAttribute()
    {
        // Accéder directement à la propriété ou la colonne de la base de données
        return (bool) $this->attributes['is_completed'];
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
     * Scope to get overdue reminders.
     */
    public function scopeOverdue($query)
    {
        return $query->where(function ($query) {
            $query->where('reminder_date', '<', now()->toDateString())
                ->orWhere(function ($query) {
                    $query->where('reminder_date', now()->toDateString())
                          ->where('reminder_time', '<', now()->toTimeString());
                });
        })
        ->where('is_completed', false);
    }


    /**
     * Get time remaining until reminder
     */
    /**
     * Get time remaining until reminder
     */
    public function getTimeRemainingAttribute()
    {
        // Assurez-vous que reminder_date et reminder_time sont des objets Carbon
        $reminderDate = $this->reminder_date instanceof \Carbon\Carbon ? $this->reminder_date->copy() : \Carbon\Carbon::parse($this->reminder_date);
        $reminderTime = $this->reminder_time instanceof \Carbon\Carbon ? $this->reminder_time->copy() : \Carbon\Carbon::parse($this->reminder_time);

        if ($reminderDate && $reminderTime) {
            try {
                $reminderDateTime = $reminderDate->setTime($reminderTime->hour, $reminderTime->minute, $reminderTime->second);
                $now = \Carbon\Carbon::now();
                $diffInSeconds = $now->diffInSeconds($reminderDateTime, false);

                if ($this->is_completed) {
                    return ['status' => 'Terminé', 'class' => 'text-gray-600'];
                } elseif ($diffInSeconds > 600) { // Plus de 10 minutes (600 secondes)
                    return ['status' => 'Pas encore arrivé', 'class' => 'text-green-600'];
                } elseif ($diffInSeconds > 0) { // Entre 0 et 10 minutes
                    return ['status' => 'moins de ' . ceil($diffInSeconds / 60) . ' min', 'class' => 'text-yellow-600 animate-pulse'];
                } elseif ($diffInSeconds >= -600) { // Arrivé ou en retard de moins de 10 minutes
                    return ['status' => 'Arrivé', 'class' => 'text-red-600 font-bold'];
                } else { // En retard de plus de 10 minutes
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

        // Assurez-vous que reminder_date et reminder_time sont des objets Carbon
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
                // Convertir les valeurs en chaînes au cas où elles soient des objets
                $dateValue = $this->reminder_date;
                $timeValue = $this->reminder_time;

                if (is_object($dateValue) && method_exists($dateValue, 'format')) {
                    $dateValue = $dateValue->format('Y-m-d');
                }

                if (is_object($timeValue) && method_exists($timeValue, 'format')) {
                    $timeValue = $timeValue->format('H:i:s');
                }

                // S'assurer que les valeurs sont des chaînes
                $dateValue = (string)$dateValue;
                $timeValue = (string)$timeValue;

                // Vérifier que les formats sont corrects
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateValue) &&
                    preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $timeValue)) {

                    $combined = $dateValue . ' ' . $timeValue;
                    $datetime = \Carbon\Carbon::parse($combined);
                    return $datetime->format('d/m/Y H:i');
                }
            } catch (\Exception $e) {
                // Ne pas afficher l'erreur pour éviter les erreurs d'affichage
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
            // Vérifier si la date ou l'heure du rappel a changé
            if ($reminder->isDirty(['reminder_date', 'reminder_time'])) {
                // Réinitialiser email_sent_at pour que la notification puisse être envoyée à nouveau
                $reminder->email_sent_at = null;
            }
        });

        static::created(function ($reminder) {
            // Créer un job différé pour envoyer l'email au moment du rappel
            $reminder->scheduleReminderEmail();
        });

        static::updated(function ($reminder) {
            // Si la date ou l'heure a été modifiée, créer un nouveau job différé
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
        // Ne planifier l'email que si le rappel est actif et non complété
        if (!$this->is_active || $this->is_completed) {
            return;
        }

        // Vérifier que la date et l'heure sont définies
        if (!$this->reminder_date || !$this->reminder_time) {
            return;
        }

        // Calculer la date et l'heure complète du rappel
        // Gérer correctement la date et l'heure qui peuvent être des objets Carbon ou des chaînes
        $date = $this->reminder_date;
        $time = $this->reminder_time;

        if ($date instanceof Carbon) {
            $date = $date->format('Y-m-d');
        }

        if ($time instanceof Carbon) {
            $time = $time->format('H:i:s');
        } elseif (is_string($time) && strlen($time) === 5) { // Si format H:i
            $time .= ':00'; // Ajouter les secondes
        }

        $reminderDateTime = Carbon::parse("{$date} {$time}");

        // Envoyer l'email 10 minutes avant le rappel
        $sendTime = $reminderDateTime->subMinutes(10);

        // Ne planifier que si la date d'envoi est dans le futur
        if ($sendTime->isFuture()) {
            SendReminderEmail::dispatch($this->id)->onQueue('default')->delay($sendTime);
        } else {
            // Si la date est dans le passé, envoyer immédiatement
            SendReminderEmail::dispatch($this->id)->onQueue('default');
        }
    }

    /**
     * Reschedule a reminder email job when the reminder is updated
     */
    public function rescheduleReminderEmail()
    {
        // Annuler les jobs existants pour ce rappel (nécessite une implémentation spécifique)
        // Pour l'instant, nous allons simplement planifier un nouveau job
        $this->scheduleReminderEmail();
    }
}