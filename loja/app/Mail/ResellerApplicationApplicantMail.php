<?php

namespace App\Mail;


use App\Models\ResellerApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class ResellerApplicationApplicantMail extends Mailable
{
    use Queueable, SerializesModels;

    public ResellerApplication $application;

    public function __construct(ResellerApplication $application)
    {
        $this->application = $application;
    }

    public function build(): self
    {
        // Gerar PDF personalizado do contrato
        $pdf = Pdf::loadView('pdf.contrato-revendedor', [
            'application' => $this->application
        ]);
        $pdfContent = $pdf->output();

        return $this
            ->subject('Recebemos o seu pedido de revenda AngolaWiFi')
            ->view('emails.reseller-application-applicant')
            ->attachData($pdfContent, 'CONTRATO_DE_AGENTE_REVENDEDOR.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
