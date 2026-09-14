<?php

namespace App\Support;

class NetworkHelper
{
    public static function getLanIp(): ?string
    {
        if (! function_exists('socket_create')) {
            return null;
        }

        $sock = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if ($sock === false) {
            return null;
        }

        @socket_connect($sock, '8.8.8.8', 80);
        @socket_getsockname($sock, $addr);
        @socket_close($sock);

        return is_string($addr) && $addr !== '127.0.0.1' ? $addr : null;
    }

    public static function mobileLoginUrl(bool $https = false): ?string
    {
        $ip = self::getLanIp();
        if (! $ip) {
            return null;
        }

        $port = $https ? (int) env('MOBILE_HTTPS_PORT', 8443) : (int) env('MOBILE_HTTP_PORT', 8000);
        $scheme = $https ? 'https' : 'http';

        return "{$scheme}://{$ip}:{$port}/login";
    }
}
