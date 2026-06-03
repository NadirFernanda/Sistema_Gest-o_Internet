<?php

namespace App\Mail;

use App\Models\InstallationAppointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InstallationAppointmentMail extends Mailable
{
    use Queueable, SerializesModels;

    public InstallationAppointment $appointment;

    public function __construct(InstallationAppointment $appointment)
    {
        $this->appointment = $appointment;
    }

    public function build(): self
    {
        $contractPath = resource_path('contracts/contrato-comodato-equipamento.pdf');

        $mail = $this
            ->subject('Confirmação do seu pedido de instalação — AngolaWiFi')
            ->view('emails.installation-appointment');

        if (file_exists($contractPath)) {
            $mail->attach($contractPath, [
                'as'   => 'Contrato_Comodato_Equipamento_AngolaWiFi.pdf',
                'mime' => 'application/pdf',
            ]);
        }

        return $mail;
    }
}
