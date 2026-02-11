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
    public $attachments = [];

    /**
     * Create a new message instance.
     */
    public function __construct($cliente, array $attachments = [])
    {
        $this->cliente = $cliente;
        $this->attachments = $attachments; // array of ['content'=>, 'name'=>, 'mime'=>]
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $mail = $this->subject('Ficha do Cliente #' . $this->cliente->id)
            ->view('emails.ficha_cliente')
            ->with(['cliente' => $this->cliente]);

        foreach ($this->attachments as $att) {
            if (!empty($att['content']) && !empty($att['name'])) {
                $mime = $att['mime'] ?? 'application/pdf';
                $mail->attachData($att['content'], $att['name'], ['mime' => $mime]);
            }
        }

        return $mail;
    }
}
