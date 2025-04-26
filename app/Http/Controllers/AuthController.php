<?php

namespace App\Http\Controllers;
use App\Events\UserRequestedPassword;
use App\Models\User;
use App\Events\UserRegistered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
class AuthController extends Controller
{

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
        // event(new UserRegistered($user));
        $token = $user->createToken($request->email);

        return response()->json(['message' => 'User registered successfully', 'data' => $user
        , 'token' => $token->plainTextToken]
        , 201);
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

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken($request->email);

        event(new UserRegistered($user));
        return response()->json(['message' => 'Logged In successfully',
         'data' => $user,
         'token'=>$token->plainTextToken], 200);
    }
    public function logout(Request $request)
    {

        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logout successful'], 200);
    }

    public function forgotPassword(Request $request)
    {

        $request->validate([
            'email' => 'required|email|exists:users',
        ]);

        $user = User::where('email', $request->email)->first();
        if (! $user) {
            return response()->json(['message' => 'Email not found'], 404);
        }

        // $token = $user->createToken($request->email)->plainTextToken;
        $token = base64_encode(Str::random(40));
        $resetURL = url('/api/reset-password/' . $token);

        $user->reset_token = $token;
        $user->reset_token_expires_at = now()->addHours(24);
        $user->save();

        event(new UserRequestedPassword($user,$resetURL));
        return response()->json(['message' => 'Password reset link sent'
        ,'resetURL'=>$resetURL], 200);
    }
    public function resetPassword(Request $request, $token)
    {

        $request->validate([
            'email' => 'required|email|exists:users',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::where('reset_token', $token)->first();
    
        if (! $user) {
            return response()->json(['message' => 'Invalid token.'], 404);
        }
        if ($user->reset_token_expires_at && $user->reset_token_expires_at < now()) {
            return response()->json(['message' => 'Token has expired.'], 400);
        }
        
        $user->password = bcrypt($request->password);
        $user->reset_token = null;
        $user->reset_token_expires_at = null;
        $user->save();
        
    
        return response()->json(['message' => 'Password reset successfully.']);
    }

}
