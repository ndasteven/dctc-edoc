<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reminder;
use App\Mail\ReminderNotification;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CheckRemindersForEmails extends Command
{
    protected $signature = 'reminders:check-for-emails';

    protected $description = 'Check for upcoming reminders and send email notifications.';

    public function handle()
    {
        $now = Carbon::now();

        // Récupérer les rappels imminents (dans les 10 prochaines minutes) qui n'ont pas encore été notifiés
        $reminders = Reminder::with(['user'])
            ->where('is_completed', false)
            ->whereNotNull('reminder_date')
            ->whereNotNull('reminder_time')
            ->whereNull('email_sent_at') // N'envoyer l'email que si ce n'a pas déjà été fait
            ->get();

        foreach ($reminders as $reminder) {
            try {
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

                $reminderDateTime = Carbon::parse("{$date} {$time}");

                // Calculer la différence en minutes entre maintenant et le rappel
                $diffInMinutes = $now->diffInMinutes($reminderDateTime, false);

                // Vérifier si le rappel est imminent (dans les 10 prochaines minutes)
                if ($diffInMinutes <= 10 && $diffInMinutes > 0) {
                    if ($reminder->user && $reminder->user->email) {
                        // Utiliser une transaction pour garantir l'atomicité
                        DB::transaction(function () use ($reminder, $now) {
                            // Mettre à jour le champ email_sent_at dans la base de données avant d'envoyer l'email
                            $updatedRows = Reminder::where('id', $reminder->id)
                                ->whereNull('email_sent_at')  // S'assurer qu'il n'a pas été mis à jour entre-temps
                                ->update(['email_sent_at' => $now]);

                            // Ne procéder à l'envoi que si la mise à jour a réussi (1 ligne affectée)
                            if ($updatedRows > 0) {
                                Mail::to($reminder->user->email)->send(new ReminderNotification($reminder));
                                $this->info("Email envoyé pour le rappel ID: {$reminder->id} à l'utilisateur: {$reminder->user->email}");
                            } else {
                                $this->info("Email déjà envoyé ou en cours d'envoi pour le rappel ID: {$reminder->id}");
                            }
                        });
                    }
                }
            } catch (\Exception $e) {
                \Log::error("Erreur lors de l'analyse ou de l'envoi du mail pour le rappel ID: {$reminder->id}", [
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->info('Rappels vérifiés et e-mails envoyés.');
    }
}
