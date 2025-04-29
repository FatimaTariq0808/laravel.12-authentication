<?php

namespace App\Services;

use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TokenService
{
    public function generateToken($user)
    {
        $plainToken = Str::random(60);
        $hashedToken = hash('sha256', $plainToken);

        $user->tokens()->create([
            'name' => 'custom-token',
            'token' => $hashedToken,
            'expires_at' => now()->addHour(),
        ]);

        return [
            'token' => $plainToken,
            'expires_at' => now()->addHour()->toDateTimeString(),
        ];
    }

    public function validateToken(string $plainToken)
    {
        $hashedToken = hash('sha256', $plainToken);

        $token = PersonalAccessToken::where('token', $hashedToken)->first();

        if (!$token || $token->expires_at->isPast()) {
            return null;
        }

        return $token;
    }

    public function invalidateToken(string $plainToken): bool
    {
        $hashedToken = hash('sha256', $plainToken);
    
        
        $token = PersonalAccessToken::where('token', $hashedToken)->first();
    
        if ($token) {
            PersonalAccessToken::where('tokenable_type', get_class($token->tokenable))
                               ->where('tokenable_id', $token->tokenable_id)
                               ->delete();
    
            return true;
        }
    
        return false;
    }
    
}
