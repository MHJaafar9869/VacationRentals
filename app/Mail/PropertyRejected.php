<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PropertyRejected extends Mailable
{
    use Queueable, SerializesModels;

    public $owner;

    public function __construct($owner)
    {
        $this->owner = $owner;
    }

    public function build()
    {
        return $this->subject('Your Property Has Been Rejected')
                    ->view('property_rejected',['owner' => $this->owner]); // Your email view for rejected
    }
}
