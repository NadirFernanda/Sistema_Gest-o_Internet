<?php

namespace App\Mail;

use App\Models\ResellerApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResellerMaintenancePaidMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ResellerApplication $application,
        public int $year,
        public int $month,
        public int $bonusAoa,
    ) {}

    public function build(): self
    {
        return $this
            ->subject('Taxa de manutenção confirmada — Vouchers creditados | AngolaWiFi')
            ->view('emails.reseller-maintenance-paid');
    }
}
