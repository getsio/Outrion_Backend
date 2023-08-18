<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
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
                'identifier' => 'required|string', // This can be either email or username
                'password' => 'required|string|min:6',
            ]);

            $credentials = $request->only('identifier', 'password');
            
            // Check if the provided identifier is an email address
            $field = filter_var($credentials['identifier'], FILTER_VALIDATE_EMAIL) ? 'email' : 'name';
            $credentials[$field] = $credentials['identifier'];
            unset($credentials['identifier']);

            if (Auth::attempt($credentials)) {
                $user = Auth::user()->load('roles');
                $token = $user->createToken('api_token')->plainTextToken;
                return response()->json(['message' => 'Login successful', 'user' => $user, 'token' => $token]);
            }

            return response()->json(['message' => 'Die E-Mail-Adresse, der Benutzername oder das Passwort ist nicht korrekt.'], 401);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        }
    }


    public function getAllUsers()
    {
        return response()->json(User::all());
    }

    public function initial($initial)
    {
        $users = User::where('name', 'LIKE', $initial . '%')->take(10)->get();

        return response()->json($users);
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
        $user = User::find($userId);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $roleIds = $request->input('roles'); // Array mit ausgewählten Rollen-IDs
        
        // Überprüfung, ob alle ausgewählten Rollen-IDs existieren
        $existingRoleIds = Role::whereIn('id', $roleIds)->pluck('id');
        $nonExistingRoleIds = array_diff($roleIds, $existingRoleIds->toArray());

        if (!empty($nonExistingRoleIds)) {
            return response()->json(['message' => 'One or more roles do not exist'], 422);
        }

        $user->roles()->sync($roleIds); // Synchronisieren der Rollen
        return response()->json(['message' => 'Roles assigned'], 200);
    }

    public function removeRole($userId, $roleId)
    {
        $user = User::find($userId);
        $role = Role::find($roleId);

        if (!$user && !$role) {
            return response()->json(['message' => 'User and Role not found'], 404);
        }

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if (!$role) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        $user->roles()->detach($role);
        return response()->json(['message' => 'Role removed from user'], 200);
    }

    public function destroy($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        
        //Löst den User von jeder Rolle
        $user->roles()->detach(); 
        $user->delete();

        return response()->json(['message' => 'User deleted'], 200);
    }

    public function indexWithRoles()
    {
        $usersWithRoles = User::with('roles')->get();

        return response()->json($usersWithRoles, 200);
    }

    public function getAuthenticatedUserRoles(Request $request)
    {
        $user = $request->user();
        $userWithRoles = $user->load('roles'); // Lädt die Beziehung 'roles' des Benutzers
    
        return response()->json($userWithRoles, 200);
    }

    
    public function update(Request $request, $userId)
    {
        try {

            $user = User::find($userId);
    
            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            $data = $request->validate([
                'name' => 'sometimes|required|string|unique:users,name|regex:/^\S*$/',
                'email' => 'sometimes|required|email|unique:users,email',
                'first_name' => 'sometimes|string',
                'last_name' => 'sometimes|string',
            ]);
    
            $user->update($data);
        
            return response()->json(['message' => 'User information updated successfully']);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        }
    }
}
