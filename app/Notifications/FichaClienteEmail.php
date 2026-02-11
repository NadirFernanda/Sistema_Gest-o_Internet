<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class FichaClienteEmail extends Notification
{
    use Queueable;

    public $cliente;
    public $attachments = [];

    public function __construct($cliente, array $attachments = [])
    {
        $this->cliente = $cliente;
        $this->attachments = $attachments;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $fileName = 'ficha_cliente_' . ($this->cliente->id ?? '0') . '.pdf';
        $nomeCliente = $this->cliente->nome ?? 'Cliente';
        if (!mb_detect_encoding($nomeCliente, 'UTF-8', true)) {
            $nomeCliente = mb_convert_encoding($nomeCliente, 'UTF-8');
        }

        $mail = (new MailMessage)
            ->subject('Ficha do Cliente #' . ($this->cliente->id ?? ''))
            ->view('emails.ficha_cliente', ['cliente' => $this->cliente]);

        foreach ($this->attachments as $att) {
            if (!empty($att['content']) && !empty($att['name'])) {
                $mime = $att['mime'] ?? 'application/pdf';
                $mail->attachData($att['content'], $att['name'], ['mime' => $mime]);
            }
        }

        return $mail;
    }
}
