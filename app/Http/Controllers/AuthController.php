<?php

namespace App\Http\Controllers;
use App\Events\UserRequestedPassword;
use App\Models\User;
use App\Events\UserRegistered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\ApiToken;
use App\Services\JWTService;
class AuthController extends Controller
{
    protected $tokenService;
    public function __construct(JWTService $tokenService)
    {
        $this->tokenService = $tokenService;
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
        event(new UserRegistered($user));
        // $token = $user->createToken($request->email);

        // $plainTextToken = Str::random(60);
        $jwt = $this->tokenService->generateToken($user);
        ApiToken::where('user_id', $user->id)->delete();
        ApiToken::create([
            'user_id' => $user->id,
            'token' => hash('sha256', $jwt), 
            'expires_at' => now()->addHours(24), 
        ]);

        return response()->json(
            [
                'message' => 'User registered successfully',
                'data' => $user
                ,
                'token' => $jwt
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

        // $token = $user->createToken($request->email);
        // $plainTextToken = Str::random(60);
        $jwt = $this->tokenService->generateToken($user);

        // $hashedToken = hash('sha256', $jwt);
        ApiToken::where('user_id', $user->id)->delete();
        ApiToken::create([
            'user_id' => $user->id,
            'token' => hash('sha256', $jwt), 
            'expires_at' => now()->addHours(24), 
        ]);
        // echo $hashedToken;
        event(new UserRegistered($user));
        return response()->json([
            'message' => 'Logged In successfully',
            'data' => $user,
            'token' => $jwt
        ], 200);
    }

    public function logout(Request $request)
    {
        $authHeader = $request->header('Authorization');
        // echo $authHeader;
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['message' => 'Token not provided'], 401);
        }
        $parts = explode(' ', $authHeader);
        $jwt = trim($parts[2]);
        // $hashedToken = hash('sha256', $plainToken);
        // echo $plainToken,$hashedToken;


        if (!$this->tokenService->invalidateToken($jwt)) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        // $token = ApiToken::where('token', $hashedToken)->first();
        // echo $token;
        // if (!$token) {
        //     return response()->json(['message' => 'Invalid token'], 401);
        // }
    
        // $token->delete(); 

        return response()->json(['message' => 'Logout successful'], 200);
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

        // $token = $user->createToken($request->email)->plainTextToken;
        $token = base64_encode(Str::random(40));
        $resetURL = url('/api/reset-password/' . $token);

        $user->reset_token = $token;
        $user->reset_token_expires_at = now()->addHours(24);
        $user->save();

        event(new UserRequestedPassword($user, $resetURL));
        return response()->json([
            'message' => 'Password reset link sent'
            ,
            'resetURL' => $resetURL
        ], 200);
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
