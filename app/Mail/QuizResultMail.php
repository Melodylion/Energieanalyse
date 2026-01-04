<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class QuizResultMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pdfContent;
    public $emailSubject;
    public $emailBody;

    public function __construct($pdfContent, $emailSubject, $emailBody)
    {
        $this->pdfContent = $pdfContent;
        $this->emailSubject = $emailSubject;
        $this->emailBody = $emailBody;
    }

    public function build()
    {
        return $this->subject($this->emailSubject)
                    ->view('emails.quiz_result')
                    ->with(['body' => $this->emailBody])
                    ->attachData($this->pdfContent, 'nervensystem-kompass.pdf', [
                        'mime' => 'application/pdf',
                    ]);
    }
}
