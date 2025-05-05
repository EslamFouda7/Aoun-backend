<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $email;

    public function __construct($token, $email)
    {
        $this->token = $token;
        $this->email = $email;
    }

    public function build()
    {
        $url = "https://aoun-1.netlify.app/reset-password/?token={$this->token}&email={$this->email}";

        return $this->subject('Reset Password')
                    ->view('emails.reset-password-plain')
                    ->with(['url' => $url]);
    }
}
