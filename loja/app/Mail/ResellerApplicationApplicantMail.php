<?php

namespace App\Mail;


use App\Models\ResellerApplication;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

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
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $html = view('pdf.contrato-revendedor', ['application' => $this->application])->render();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdfContent = $dompdf->output();

        return $this
            ->subject('Recebemos o seu pedido de revenda AngolaWiFi')
            ->view('emails.reseller-application-applicant')
            ->attachData($pdfContent, 'CONTRATO_DE_AGENTE_REVENDEDOR.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
