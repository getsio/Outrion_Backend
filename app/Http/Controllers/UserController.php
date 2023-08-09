<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|unique:users,name|regex:/^\S*$/', // Benutzername muss eindeutig sein
                'email' => 'required|email|unique:users,email', // E-Mail-Adresse muss eindeutig sein
                'password' => 'required|string|min:6',
            ]);
    
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
    
            return response()->json(['message' => 'Registration successful', 'user' => $user]);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        }
    }
    

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);
    
            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                $user = Auth::user();
                $token = $user->createToken('api_token')->plainTextToken;
                return response()->json(['message' => 'Login successful', 'user' => $user, 'token' => $token]);
            }
    
            return response()->json(['message' => 'Die Email Adresse oder das Passwort ist nicht korrekt.'], 401);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        }
    }

    public function getAllUsers()
    {
        return response()->json(User::all());
    }

    public function getAuthenticatedUser(Request $request)
    {
        return response()->json($request->user());
    }

    public function changePassword(Request $request)
    {
        try {
            $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:6|different:current_password',
                'new_password_confirmation' => 'required|string|same:new_password',
            ]);
    
            $user = Auth::user();
    
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json(['error' => 'Das aktuelle Passwort ist nicht korrekt.'], 422);
            }
    
            $user->password = Hash::make($request->new_password);
            $user->save();
    
            return response()->json(['message' => 'Passwort erfolgreich geändert.'], 200);
        }catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        }
    }

    public function logout()
    {
        $currentToken = request()->user()->currentAccessToken();
    
        if ($currentToken) {
            $currentToken->delete();
            return response()->json(['message' => 'Logout erfolgreich.'], 200);
        } else {
            return response()->json(['message' => 'Kein aktuelles Token gefunden.'], 404);
        }
    }

    public function assignRole(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $roleIds = $request->input('roles'); // Array mit ausgewählten Rollen-IDs
        $user->roles()->sync($roleIds); // Synchronisieren der Rollen
        return response()->json(['message' => 'Roles assigned'], 200);
    }
}
