<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Auth\Notifications\VerifyEmail;

class QueuedVerifyEmail extends VerifyEmail implements ShouldQueue
{
    use Queueable;
    
    // We inherit toMail from parent VerifyEmail
}
