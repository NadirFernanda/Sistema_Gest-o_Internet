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

    public function __construct(ResellerApplication $application)
    {
        $this->application = $application;
    }

    public function build(): self
    {
        $subject = $this->application->status === 'approved'
            ? 'A sua candidatura foi aprovada — AngolaWiFi'
            : 'Actualização sobre a sua candidatura — AngolaWiFi';

        return $this
            ->subject($subject)
            ->view('emails.reseller-status');
    }
}
