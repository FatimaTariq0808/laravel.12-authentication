<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Jobs\SendWelcomeEmail;
use App\Services\TokenService;
use App\Services\PasswordResetService;
class AuthController extends Controller
{

    protected $tokenService;
    protected $passwordResetService;

    public function __construct(TokenService $tokenService, PasswordResetService $passwordResetService)
    {
        $this->tokenService = $tokenService;
        $this->passwordResetService = $passwordResetService;
    }

    public function register(Request $request)
    {
        // Validating the request
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);


        return response()->json(
            [
                'message' => 'User registered successfully',
                'data' => $user
                ,
            ]
            ,
            201
        );
    }
    public function login(Request $request)
    {
        // Validating the request
        $request->validate([
            'email' => 'required|email:exists',
            'password' => 'required',
        ]);
        // dd($request->all());
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $tokenData = $this->tokenService->generateToken($user);
        SendWelcomeEmail::dispatch($user);
        
        return response()->json([
            'message' => 'Logged in successfully',
            'data' => $user,
            'token' => $tokenData['token'],
            'expires_at' => $tokenData['expires_at'],
        ], 200);

    }


    public function logout(Request $request)
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['message' => 'Token not provided'], 401);
        }
        $parts = explode(' ', $authHeader);
        $plainToken = trim($parts[2]);
        
        // echo '' . $plainToken . '';


        $this->tokenService->invalidateToken($plainToken);

        return response()->json(['message' => 'Logged out']);
    }


    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'Email not found'], 404);
        }

        // Call the service to send the reset link
        return $this->passwordResetService->sendResetLink($user);
    }
    public function resetPassword(Request $request, $token)
    {

        $request->validate([
            'email' => 'required|email|exists:users',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::where('reset_token', $token)->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid token.'], 404);
        }
        if ($user->reset_token_expires_at && $user->reset_token_expires_at < now()) {
            return response()->json(['message' => 'Token has expired.'], 400);
        }
        if ($user->email !== $request->email) {
            return response()->json(['message' => 'Email does not match the reset request.'], 400);
        }

        $user->password = bcrypt($request->password);
        $user->reset_token = null;
        $user->reset_token_expires_at = null;
        $user->save();


        return response()->json(['message' => 'Password reset successfully.']);
    }

}
