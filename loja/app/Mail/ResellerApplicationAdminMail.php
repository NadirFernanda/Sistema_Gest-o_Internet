<?php

namespace App\Mail;

use App\Models\ResellerApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResellerApplicationAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public ResellerApplication $application;

    public function __construct(ResellerApplication $application)
    {
        $this->application = $application;
    }

    public function build(): self
    {
        return $this
            ->subject('Novo pedido de revenda AngolaWiFi')
            ->view('emails.reseller-application-admin');
    }
}
