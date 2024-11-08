<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewBookProperty extends Notification
{
    use Queueable;
    public $user;
    public $property;


    /**
     * Create a new notification instance.
     */
    public function __construct($user, $property)
    {
        //
        $this->user = $user;
        $this->property = $property;
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
            'property_id' => $this->property->id,
            'name' => $this->user->name,
            'message' => 'New property booked By : ' . $this->user->name,
        ];
    }
}
