<?php

// Simple JWT helper (HS256) without external dependencies.
// NOTE: This is intentionally minimal for LAB needs.

class Jwt
{
    public static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }

    public static function sign(array $payload, string $secret): string
    {
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256',
        ];

        $segments = [];
        $segments[] = self::base64UrlEncode(json_encode($header, JSON_UNESCAPED_UNICODE));
        $segments[] = self::base64UrlEncode(json_encode($payload, JSON_UNESCAPED_UNICODE));

        $signingInput = $segments[0] . '.' . $segments[1];
        $signature = hash_hmac('sha256', $signingInput, $secret, true);
        $segments[] = self::base64UrlEncode($signature);

        return implode('.', $segments);
    }

    public static function verify(string $jwt, string $secret): array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            return ['valid' => false, 'error' => 'JWT_FORMAT'];
        }

        [$h64, $p64, $s64] = $parts;
        $signingInput = $h64 . '.' . $p64;
        $expected = self::base64UrlEncode(hash_hmac('sha256', $signingInput, $secret, true));

        if (!hash_equals($expected, $s64)) {
            return ['valid' => false, 'error' => 'JWT_SIGNATURE'];
        }

        $payloadRaw = self::base64UrlDecode($p64);
        $payload = json_decode($payloadRaw, true);
        if (!is_array($payload)) {
            return ['valid' => false, 'error' => 'JWT_PAYLOAD'];
        }

        if (isset($payload['exp']) && time() >= (int)$payload['exp']) {
            return ['valid' => false, 'error' => 'JWT_EXPIRED'];
        }

        return ['valid' => true, 'payload' => $payload];
    }
}

