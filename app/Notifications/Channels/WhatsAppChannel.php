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
            // If the service returned a falsy value, treat as failure so callers can catch it
            if (!$result) {
                Log::warning('WhatsAppChannel send returned falsy result', ['notifiable' => $notifiable, 'result' => $result]);
                throw new \RuntimeException('WhatsApp send failed');
            }
            return $result;
        } catch (\Throwable $e) {
            Log::error('WhatsAppChannel send error', ['err' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw $e;
        }

        return false;
    }
}
