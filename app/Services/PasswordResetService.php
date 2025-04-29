<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendResetPasswordEmail;

class PasswordResetService
{
    public function sendResetLink(User $user)
    {
        try {
            
            $token = base64_encode(Str::random(40)); 
            $resetURL = url('/api/reset-password/' . $token);
            $user->reset_token = $token;
            $user->reset_token_expires_at = now()->addHours(); 
            $user->save();

            
            SendResetPasswordEmail::dispatch($user, $resetURL);

            return response()->json([
                'message' => 'Password reset link sent.',
                'resetURL' => $resetURL
            ], 200);
        } catch (\Exception $e) {
            Log::error('Password Reset Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to send password reset link.',
            ], 500);
        }
    }
}
