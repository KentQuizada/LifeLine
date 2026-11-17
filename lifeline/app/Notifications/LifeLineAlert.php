<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * The email copy of an in-app alert. Laravel notifications are used for mail only; in-app alerts are
 * rows in LifeLine's own notifications table (DEC-011). Sent right away, no queue worker (DEC-001).
 */
class LifeLineAlert extends Notification
{
    public function __construct(
        public readonly string $title,
        public readonly string $message,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('LifeLine: '.$this->title)
            ->greeting($this->title)
            ->line($this->message)
            ->action('Open LifeLine', route('alerts.index'))
            ->line('LifeLine only suggests potential donors. Final compatibility and eligibility are confirmed by healthcare professionals.');
    }
}
