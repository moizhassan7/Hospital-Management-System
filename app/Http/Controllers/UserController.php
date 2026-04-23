<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function showLoginForm()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    public function manager(User $user = null)
    {
        $users = User::all();
        $roles = Role::all();
        $permissions = Permission::all()->groupBy('group_name');
        
        return view('users.manager', compact('user', 'users', 'roles', 'permissions'));
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'username' => 'required|string|max:255|unique:users,username,' . ($request->user_id ?? 'NULL') . ',id',
        'email' => 'nullable|email|unique:users,email,' . ($request->user_id ?? 'NULL') . ',id',
        'branch' => 'nullable|string|max:255',
        'password' => $request->user_id ? 'nullable|min:6' : 'required|min:6',
        'permissions' => 'array',
    ]);

    // If user_id is present, we’re updating an existing user
    $user = $request->user_id ? User::find($request->user_id) : new User();

    $user->name = $validated['name'];
    $user->username = $validated['username'];
    $user->email = $validated['email'] ?? $user->email;
    $user->branch = $validated['branch'] ?? '';
    
    // Update password only if provided
    if (!empty($validated['password'])) {
        $user->password = bcrypt($validated['password']);
    }

    $user->save();

    // Sync Permissions
    $user->permissions()->sync($validated['permissions'] ?? []);

    return redirect()->route('admin.user_manager')->with('success', 'User saved successfully!');
}

}