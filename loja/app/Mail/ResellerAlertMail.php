<?php

namespace App\Mail;

use App\Models\ResellerApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResellerAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    /** @param string $alertType  'maintenance' | 'target' */
    public function __construct(
        public ResellerApplication $application,
        public string $alertType,
    ) {}

    public function build(): self
    {
        $subject = match ($this->alertType) {
            'maintenance' => 'AngolaWiFi – Taxa de manutenção em atraso',
            'target'      => 'AngolaWiFi – Meta mensal ainda não atingida',
            default       => 'AngolaWiFi – Alerta da sua conta de revendedor',
        };

        return $this
            ->subject($subject)
            ->view('emails.reseller-alert')
            ->with([
                'application' => $this->application,
                'alertType'   => $this->alertType,
            ]);
    }
}
