<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     * Set specific reverse-proxy IPs via TRUSTED_PROXIES env variable (comma-separated).
     * Never use '*' in production as it allows IP spoofing via X-Forwarded-For.
     *
     * @var array|string|null
     */
    protected $proxies = null;

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers = Request::HEADER_X_FORWARDED_ALL;

    public function __construct()
    {
        $envProxies = env('TRUSTED_PROXIES');
        if (!empty($envProxies)) {
            $this->proxies = array_map('trim', explode(',', $envProxies));
        }
    }
}
