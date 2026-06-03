<?php

namespace App\Mail;

use App\Models\ResellerApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResellerStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public ResellerApplication $application;
    public ?string $rejectionReason;

    public function __construct(ResellerApplication $application, ?string $rejectionReason = null)
    {
        $this->application     = $application;
        $this->rejectionReason = $rejectionReason;
    }

    public function build(): self
    {
        $isApproved = $this->application->status === 'approved';

        $subject = $isApproved
            ? 'A sua candidatura foi aprovada — AngolaWiFi'
            : 'Actualização sobre a sua candidatura — AngolaWiFi';

        $mail = $this
            ->subject($subject)
            ->view('emails.reseller-status')
            ->with(['rejectionReason' => $this->rejectionReason]);

        if ($isApproved) {
            $contractPath = resource_path('contracts/contrato-agente-revendedor.pdf');
            if (file_exists($contractPath)) {
                $mail->attach($contractPath, [
                    'as'   => 'Contrato_Agente_Revendedor_AngolaWiFi.pdf',
                    'mime' => 'application/pdf',
                ]);
            }
        }

        return $mail;
    }
}
