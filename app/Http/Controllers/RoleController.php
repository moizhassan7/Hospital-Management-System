<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return view('roles.index', compact('roles'));
    }

    public function create(Role $role = null)
    {
        $permissions = Permission::all()->groupBy('group_name');
        return view('roles.create', compact('role', 'permissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . ($request->role_id ?? 'NULL') . ',id',
            'permissions' => 'required|array',
        ]);

        $role = $request->role_id ? Role::find($request->role_id) : new Role();
        $role->name = $validated['name'];
        $role->save();

        $role->permissions()->sync($validated['permissions']);

        return redirect()->route('admin.roles.index')->with('success', 'Role saved successfully!');
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'Super Admin') {
            return back()->with('error', 'Cannot delete Super Admin role.');
        }
        $role->delete();
        return redirect()->route('admin.roles.index')->with('success', 'Role deleted successfully!');
    }
}
