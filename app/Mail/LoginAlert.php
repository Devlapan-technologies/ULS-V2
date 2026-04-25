<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class LoginAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $name;
    public $ip;
    public $time;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, $ip)
    {
        $this->user = $user;
        $this->name = $user->first_name; // Get first name from HR API
        $this->ip = $ip;
        $this->time = now()->format('F j, Y, g:i a');
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('🔐 New Login to Your Account')
                    ->view('emails.login-alert');
    }
}