<?php

namespace App\Mail;

use App\Models\AutovendaOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AutovendaWifiCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public AutovendaOrder $order;

    public function __construct(AutovendaOrder $order)
    {
        $this->order = $order;
    }

    public function build(): self
    {
        return $this->subject('Seu código AngolaWiFi')->view('emails.autovenda-wifi-code');
    }
}
