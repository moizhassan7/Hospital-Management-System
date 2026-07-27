<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function showLoginForm()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required'],
        ]);

        $loginField = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (Auth::attempt([$loginField => $credentials['login'], 'password' => $credentials['password']])) {
            $request->session()->regenerate();

            // Land the user on the first module they actually have access to,
            // instead of a fixed page (or a stale "intended" URL) that may be
            // blocked by their permissions.
            return redirect()->route(Auth::user()->homeRoute());
        }

        return back()->withErrors([
            'login' => 'The provided credentials do not match our records.',
        ])->onlyInput('login');
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
        $users = User::with('roles', 'permissions')->orderBy('name')->paginate(15)->withQueryString();
        $userOptions = User::orderBy('name')->get(['id', 'name', 'username']);
        $roles = Role::with('permissions')->orderBy('name')->get();
        $permissions = Permission::orderBy('group_name')->orderBy('name')->get()->groupBy('group_name');

        $effectivePermissions = isset($user) ? $user->allPermissionNames() : [];

        return view('users.manager', compact('user', 'users', 'userOptions', 'roles', 'permissions', 'effectivePermissions'));
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

    public function showChangePasswordForm()
    {
        $user = Auth::user();
        return view('users.change-password', compact('user'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'old_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'new_password.confirmed' => 'New password confirmation does not match.',
            'new_password.min' => 'New password must be at least 6 characters.',
        ]);

        $user = Auth::user();

        if (! Hash::check($request->old_password, $user->password)) {
            return back()->withErrors([
                'old_password' => 'The provided current password does not match our records.',
            ]);
        }

        $user->password = bcrypt($request->new_password);
        $user->save();

        return back()->with('success', 'Password updated successfully!');
    }
}