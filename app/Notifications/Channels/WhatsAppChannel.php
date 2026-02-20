<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

class WhatsAppChannel
{
    protected $service;

    public function __construct(WhatsAppService $service)
    {
        $this->service = $service;
    }

    /**
     * Send the given notification.
     */
    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toWhatsApp')) {
            return false;
        }

        try {
            // Allow notification to return whatever the WhatsAppService expects
            $result = $notification->toWhatsApp($notifiable);
            return $result;
        } catch (\Throwable $e) {
            Log::error('WhatsAppChannel send error', ['err' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }

        return false;
    }
}
