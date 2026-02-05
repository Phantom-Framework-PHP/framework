<?php

namespace Phantom\Traits;

use Phantom\Notifications\Notification;
use Phantom\Core\Container;

trait Notifiable
{
    /**
     * Send the given notification.
     *
     * @param  Notification  $notification
     * @return void
     */
    public function notify($notification)
    {
        $channels = $notification->via($this);

        foreach ($channels as $channel) {
            $this->sendNotificationVia($channel, $notification);
        }
    }

    /**
     * Send the notification via the given channel.
     *
     * @param  string  $channel
     * @param  Notification  $notification
     * @return void
     */
    protected function sendNotificationVia($channel, $notification)
    {
        $app = Container::getInstance();

        if ($channel === 'database') {
            $data = $notification->toDatabase($this);
            $app->make('db')->table('notifications')->insert([
                'notifiable_id' => $this->{$this->primaryKey},
                'notifiable_type' => static::class,
                'data' => json_encode($data),
                'read_at' => null,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        if ($channel === 'mail') {
            $message = $notification->toMail($this);
            $app->make('mail')->send($message['view'], $message['data'], function($m) use ($message) {
                $m->to($this->email)->subject($message['subject']);
            });
        }

        if ($channel === 'broadcast') {
            $data = $notification->toBroadcast($this);
            if ($app->has('broadcaster')) {
                $app->make('broadcaster')->broadcast(
                    ["private-user.{$this->{$this->primaryKey}}"],
                    get_class($notification),
                    $data
                );
            }
        }
    }

    /**
     * Get the entity's notifications.
     */
    public function notifications()
    {
        return $this->morphMany(\Phantom\Models\Model::class, 'notifiable')
                    ->where('notifiable_type', static::class);
    }
}
