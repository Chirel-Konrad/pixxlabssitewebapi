<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class QueuedVerifyEmail extends VerifyEmail implements ShouldQueue
{
    use Queueable;
    
    public $url;

    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * Get the mail representation of the notification.
     */
    protected function buildMailMessage($notifiable)
    {
        return (new MailMessage)
            ->subject('Vérification de votre adresse email - Polariix')
            ->greeting('Bonjour !')
            ->line('Veuillez cliquer sur le bouton ci-dessous pour vérifier votre adresse email.')
            ->line('Ce lien est valide pendant 10 minutes.')
            ->action('Vérifier mon email', $this->url)
            ->line('Si vous n\'avez pas créé de compte, aucune action n\'est requise.')
            ->salutation('Cordialement, L\'équipe Polariix');
    }
}
