<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class PasswordChangedAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $time;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->time = now()->format('F j, Y, g:i a');
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('🔒 Your Password Was Changed' )
                    ->view('emails.password-changed');
    }
}