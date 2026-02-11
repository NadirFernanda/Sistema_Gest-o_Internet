<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FichaClienteMail extends Mailable
{
    use Queueable, SerializesModels;

    public $cliente;
    public $filename;
    public $pdfContent;

    /**
     * Create a new message instance.
     */
    public function __construct($cliente, $pdfContent, $filename)
    {
        $this->cliente = $cliente;
        $this->pdfContent = $pdfContent;
        $this->filename = $filename;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $mail = $this->subject('Ficha do Cliente #' . $this->cliente->id)
            ->view('emails.ficha_cliente')
            ->with(['cliente' => $this->cliente]);

        $mail->attachData($this->pdfContent, $this->filename, [
            'mime' => 'application/pdf',
        ]);

        return $mail;
    }
}
