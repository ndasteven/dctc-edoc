<?php

namespace App\Jobs;

use App\Mail\ReminderNotification;
use App\Models\Reminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class SendReminderEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $reminderId;

    /**
     * Create a new job instance.
     */
    public function __construct($reminderId)
    {
        $this->reminderId = $reminderId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $reminder = Reminder::find($this->reminderId);

        // Vérifier si le rappel existe et n'a pas été supprimé entre-temps
        if (!$reminder) {
            return;
        }

        // Vérifier que l'utilisateur existe et a une adresse email
        if ($reminder->user && $reminder->user->email) {
            // Utiliser une transaction pour garantir l'atomicité
            DB::transaction(function () use ($reminder) {
                // Mettre à jour le champ email_sent_at dans la base de données avant d'envoyer l'email
                $updatedRows = Reminder::where('id', $reminder->id)
                    ->whereNull('email_sent_at')  // S'assurer qu'il n'a pas été mis à jour entre-temps
                    ->update(['email_sent_at' => now()]);

                // Ne procéder à l'envoi que si la mise à jour a réussi (1 ligne affectée)
                if ($updatedRows > 0) {
                    Mail::to($reminder->user->email)->send(new ReminderNotification($reminder));
                }
            });
        }
    }
}
