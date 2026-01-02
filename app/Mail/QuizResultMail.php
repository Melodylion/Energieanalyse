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
    public $respondentName;

    /**
     * Create a new message instance.
     *
     * @param string $pdfContent The raw PDF content content
     * @param string $respondentName Name of respondent (optional)
     */
    public function __construct($pdfContent)
    {
        $this->pdfContent = $pdfContent;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Dein persönliches Nervensystem-Profil')
                    ->view('emails.quiz_result')
                    ->attachData($this->pdfContent, 'nervensystem-kompass.pdf', [
                        'mime' => 'application/pdf',
                    ]);
    }
}
