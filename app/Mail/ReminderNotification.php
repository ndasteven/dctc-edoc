<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class ReminderNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $reminder;

    public function __construct($reminder)
    {
        // Charger les relations nécessaires pour le template de mail
        $reminder->load(['document', 'folder', 'document.folder']);
        $this->reminder = $reminder;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Rappel : ' . $this->reminder->title,
        );
    }

    public function content(): Content
    {
        // Formater correctement la date et l'heure combinées
        $date = $this->reminder->reminder_date;
        $time = $this->reminder->reminder_time;

        // Gérer les objets Carbon si nécessaire
        if ($date instanceof Carbon) {
            $date = $date->format('Y-m-d');
        }

        if ($time instanceof Carbon) {
            $time = $time->format('H:i:s');
        } elseif (is_string($time) && strlen($time) === 5) { // Si format H:i
            $time .= ':00'; // Ajouter les secondes
        }

        $combinedDateTime = Carbon::parse("{$date} {$time}");
        $formattedDateTime = $combinedDateTime->format('d/m/Y H:i');

        return new Content(
            markdown: 'mail.reminder-notification',
            with: [
                'title' => $this->reminder->title,
                'message' => $this->reminder->message,
                'reminderDate' => $formattedDateTime,
                'reminder' => $this->reminder,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
