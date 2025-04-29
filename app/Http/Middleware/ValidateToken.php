<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;

class ValidateToken
{
    public function handle(Request $request, Closure $next)
    {
        \Log::info("Middleware Working");
        $authHeader = $request->header('Authorization');


        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['message' => 'Token not provided'], 401);
        }
        $parts = explode(' ', $authHeader);
        $plainToken = trim($parts[2]);
        // echo ''. $plainToken .'';
        $hashedToken = hash('sha256', $plainToken);

        // Check if a user has this token and it's not expired
        $user = User::whereHas('tokens', function ($query) use ($hashedToken) {
            $query->where('token', $hashedToken)
                  ->where('expires_at', '>', Carbon::now());
        })->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid or expired token'], 401);
        }

        $request->merge(['auth_user_id' => $user->id]);

        return $next($request);
    }
}
