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
        $users = User::with('roles', 'permissions')->get();
        $roles = Role::all();
        $permissions = Permission::all()->groupBy('group_name');
        
        return view('users.manager', compact('user', 'users', 'roles', 'permissions'));
    }

    public function store(Request $request, User $user = null)
    {
        // If $user is not provided via route binding, try to find it via user_id from request
        if (!$user && $request->user_id) {
            $user = User::find($request->user_id);
        }
        
        $userId = $user ? $user->id : 'NULL';

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $userId . ',id',
            'email' => 'nullable|email|unique:users,email,' . $userId . ',id',
            'branch' => 'nullable|string|max:255',
            'password' => $user ? 'nullable|min:6' : 'required|min:6',
            'roles' => 'array',
            'permissions' => 'array',
        ]);

        if (!$user) {
            $user = new User();
        }

        $user->name = $validated['name'];
        $user->username = $validated['username'];
        $user->email = $validated['email'] ?? $user->email;
        $user->branch = $validated['branch'] ?? '';
        
        if (!empty($validated['password'])) {
            $user->password = bcrypt($validated['password']);
        }

        $user->save();

        // Sync Roles
        $user->roles()->sync($validated['roles'] ?? []);

        // Sync Direct Permissions
        $user->permissions()->sync($validated['permissions'] ?? []);

        return redirect()->route('admin.user_manager')->with('success', 'User saved successfully!');
    }

}