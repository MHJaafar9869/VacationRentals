<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOwnerRegister extends Notification
{
    use Queueable;

    public $user;
    /**
     * Create a new notification instance.
     */
    public function __construct($user)
    {
        //
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
            'user_id' => $this->user->id,
            'type' => 'owner',
            'name' => $this->user->name,
            'message' => 'New owner registered: ' . $this->user->name,
        ];
    }
}
