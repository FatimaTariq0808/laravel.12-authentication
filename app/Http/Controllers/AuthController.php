<?php

namespace App\Http\Controllers;
use App\Events\UserRequestedPassword;
use App\Models\User;
use App\Events\UserRegistered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

        // Creating the user
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
        // Validate the request
        $request->validate([
            'email' => 'required|email|exists:users',
        ]);

        $user = User::where('email', $request->email)->first();
        if (! $user) {
            return response()->json(['message' => 'Email not found'], 404);
        }

        $token = $user->createToken($request->email)->plainTextToken;
        $resetURL = url('/api/reset-password/' . $token);

        event(new UserRequestedPassword($user,$resetURL));
        return response()->json(['message' => 'Password reset link sent'
        ,'resetURL'=>$resetURL
        ,'token'=>$token], 200);
    }
    public function resetPassword(Request $request)
    {
        // Validate the request
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Logic to reset the password
        $user = User::where('email', $request->email)->first();
        $user->password = bcrypt($request->password);
        $user->save();

        return response()->json(['message' => 'Password reset successfully'], 200);
    }

}
