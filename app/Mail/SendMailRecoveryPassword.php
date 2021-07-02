<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendMailRecoveryPassword extends Mailable
{
    use Queueable, SerializesModels;

    private $email, $code;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email, $code)
    {
        $this->email = $email;
        $this->code  = $code;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('FastGP - Recuperação de senha')
            ->with([
                'email' => $this->email,
                'code'  => $this->code
            ])
            ->view('emails.recovery-password');
    }
}
