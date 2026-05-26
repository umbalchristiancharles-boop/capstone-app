<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommentFlaggedNotification extends Notification
{
    use Queueable;

    protected $comment;
    protected $reason;

    public function __construct($comment, $reason = null)
    {
        $this->comment = $comment;
        $this->reason = $reason;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->subject('Warning: Your comment has been flagged')
            ->greeting('Hello ' . ($notifiable->full_name ?? $notifiable->email))
            ->line('One of your comments was flagged by an administrator for review.');

        if (!empty($this->reason)) {
            $mail->line('Reason: ' . $this->reason);
        }

        $mail->line('Comment: ' . (
            is_string($this->comment->text) ? mb_strimwidth($this->comment->text, 0, 300, '...') : ''
        ));

        $mail->line('If you believe this is a mistake, please contact support. Continued violations may lead to account suspension.');

        return $mail;
    }
}
