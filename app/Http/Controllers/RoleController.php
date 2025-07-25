<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        return response()->json($roles);
    }

    public function indexWithUsers()
    {
        $rolesWithUsers = Role::with('users')->get();

        return response()->json($rolesWithUsers, 200);
    }
    
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|unique:roles'
            ]);
    
            $role = Role::create($request->all());
            return response()->json($role, 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        }
    }

    public function destroy($roleId)
    {
        $role = Role::find($roleId);

        if (!$role) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        /*
        if ($role->users()->count() > 0) {
            return response()->json(['message' => 'Role is assigned to users, cannot delete'], 422);
        }
        */
        
        //Löst die Rollen von jedem User
        $role->users()->detach(); 
        $role->delete();

        return response()->json(['message' => 'Role deleted'], 200);
    }

    public function getRole($roleId)
    {
        $role = Role::find($roleId);

        if (!$role) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        $roleWithUsers = $role->load('users');

        return response()->json($roleWithUsers, 200);
    }
}
