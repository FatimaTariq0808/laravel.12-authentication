<?php

namespace App\Services;

use App\Models\ApiToken;

class JWTService
{
    protected $secretKey;

    public function __construct()
    {
        $this->secretKey = env('JWT_SECRET', '${JWT_SECRET}'); 
    }

    public function generateToken($user)
    {
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT',
        ];

        $payload = [
            'sub' => $user->id,
            'email' => $user->email,
            'iat' => time(),
            'exp' => time() + 3600, // 1 hour expiration
        ];

        $base64UrlHeader = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
        $base64UrlPayload = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');

        $signature = hash_hmac('sha256', $base64UrlHeader . '.' . $base64UrlPayload, $this->secretKey, true);
        $base64UrlSignature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        $jwt = $base64UrlHeader . '.' . $base64UrlPayload . '.' . $base64UrlSignature;

        // Save token in the database
        ApiToken::create([
            'user_id' => $user->id,
            'token' => hash('sha256', $jwt),
            'expires_at' => now()->addHour(), // match payload
        ]);

        return $jwt;
    }
    public function invalidateToken($jwt)
    {
        $hashedToken = hash('sha256', $jwt);

        $token = ApiToken::where('token', $hashedToken)->first();

        if ($token) {
            $token->delete();
            return true;
        }

        return false;
    }
    public function decode(string $jwt): ?array
    {
        $parts = explode('.', $jwt);

        if (count($parts) !== 3) {
            return null;
        }

        [$header, $payload, $signature] = $parts;

        $decodedPayload = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);

        return $decodedPayload;
    }

    public function validate(string $jwt): bool
    {
        $parts = explode('.', $jwt);

        if (count($parts) !== 3) {
            return false;
        }

        [$header, $payload, $signature] = $parts;

        $expectedSignature = $this->base64UrlEncode(
            hash_hmac('sha256', "$header.$payload", $this->secretKey, true)
        );

        return hash_equals($expectedSignature, $signature);
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}