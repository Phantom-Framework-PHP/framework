<?php

namespace Phantom\Notifications;

abstract class Notification
{
    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    abstract public function via($notifiable);

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return [];
    }

    /**
     * Get the array representation of the notification for database.
     */
    public function toDatabase($notifiable)
    {
        return [];
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast($notifiable)
    {
        return [];
    }
}
