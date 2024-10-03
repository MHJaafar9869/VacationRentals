<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PropertyAccepted extends Mailable
{
    use Queueable, SerializesModels;

    public $owner;

    public function __construct($owner)
    {
        $this->owner = $owner;
    }

    public function build()
    {
        return $this->subject('Your Property Has Been Accepted')
                    ->view('property_accepted', ['owner' => $this->owner]);
    }
}
