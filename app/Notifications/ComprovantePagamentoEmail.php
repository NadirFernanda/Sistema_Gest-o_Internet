<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Barryvdh\DomPDF\Facade\Pdf;

class ComprovantePagamentoEmail extends Notification
{
    use Queueable;
    public $cobranca;
    public function __construct($cobranca)
    {
        $this->cobranca = $cobranca;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $fileName = 'comprovativo_pagamento_' . $this->cobranca->id . '.pdf';
        $nomeCliente = $this->cobranca->cliente->nome ?? 'Cliente';
        if (!mb_detect_encoding($nomeCliente, 'UTF-8', true)) {
            $nomeCliente = mb_convert_encoding($nomeCliente, 'UTF-8');
        }
        $pdf = Pdf::loadView('cobrancas.comprovante', ['cobranca' => $this->cobranca]);
        $logoUrl = asset('img/logo.jpeg');
        return (new MailMessage)
            ->subject('Comprovativo de Pagamento')
            ->view('emails.comprovante_pagamento', [
                'nomeCliente' => $nomeCliente,
                'logoUrl' => $logoUrl,
            ])
            ->attachData($pdf->output(), $fileName, [
                'mime' => 'application/pdf',
            ]);
    }
}
