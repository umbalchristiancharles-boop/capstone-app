<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountBannedNotification extends Notification
{
    use Queueable;

    protected $reason;

    public function __construct($reason = null)
    {
        $this->reason = $reason;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->subject('Account Suspended')
            ->greeting('Hello ' . ($notifiable->full_name ?? $notifiable->email))
            ->line('Your account has been suspended due to multiple flagged comments.');

        if (!empty($this->reason)) {
            $mail->line('Reason: ' . $this->reason);
        }

        $mail->line('If you believe this is in error, please contact support.');

        return $mail;
    }
}
