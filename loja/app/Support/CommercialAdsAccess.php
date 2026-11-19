<?php

namespace App\Support;

use Illuminate\Http\Request;

final class CommercialAdsAccess
{
    private const SESSION_KEY = 'ads_commercial_grant';

    public static function enabled(): bool
    {
        return (bool) config('services.ads_commercial.enabled', false)
            && strlen(self::secret()) >= 32;
    }

    public static function authorized(Request $request): bool
    {
        if (! self::enabled() || ! $request->session()->get('sg_admin_authenticated', false)) {
            return false;
        }

        $grant = $request->session()->get(self::SESSION_KEY);

        return is_string($grant)
            && hash_equals(self::sessionGrant(), $grant);
    }

    public static function matches(string $submittedSecret): bool
    {
        return self::enabled() && hash_equals(self::secret(), $submittedSecret);
    }

    public static function grant(Request $request): void
    {
        $request->session()->regenerate();
        $request->session()->put(self::SESSION_KEY, self::sessionGrant());
    }

    public static function revoke(Request $request): void
    {
        $request->session()->forget(self::SESSION_KEY);
    }

    private static function sessionGrant(): string
    {
        return hash_hmac('sha256', 'commercial-ads-v1', self::secret());
    }

    private static function secret(): string
    {
        return (string) config('services.ads_commercial.secret', '');
    }
}
