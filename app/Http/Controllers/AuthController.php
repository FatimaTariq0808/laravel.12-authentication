<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Events\UserRegistered;  
use Illuminate\Http\Request;

class AuthController extends Controller
{
    
    public function login(Request $request)
    {
        // Validate the request
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
        // dd($request->all());
        // Attempt to log the user in
        if (auth()->attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Login successful'], 200);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    public function logout(Request $request)
    {
        // Log the user out
        auth()->logout();

        return response()->json(['message' => 'Logout successful'], 200);
    }
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
        event(new UserRegistered($user));
        
        return response()->json(['message' => 'User registered successfully','data'=>$user], 201);
    }   
}
