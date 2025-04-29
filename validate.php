<?php

namespace App\Http\Middleware;

use App\Services\JWTService;
use Closure;
use Illuminate\Http\Request;

class JWTMiddleware
{
    protected $jwtService;

    public function __construct(JWTService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    public function handle(Request $request, Closure $next)
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['message' => 'Authorization token not provided'], 401);
        }

        $token = trim(str_replace('Bearer', '', $authHeader));

        // Validate the token signature
        if (!$this->jwtService->validate($token)) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        // Decode the token
        $payload = $this->jwtService->decode($token);

        if (!$payload) {
            return response()->json(['message' => 'Invalid token payload'], 401);
        }

        // Check if token expired
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return response()->json(['message' => 'Token has expired'], 401);
        }

        // Attach payload (example: user_id) to request
        $request->merge(['user_id' => $payload['sub']]);

        return $next($request);
    }
}
