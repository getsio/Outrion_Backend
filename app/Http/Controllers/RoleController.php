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
}
