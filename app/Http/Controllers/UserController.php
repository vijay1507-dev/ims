<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        \Illuminate\Support\Facades\Gate::authorize('users.manage');

        return \Inertia\Inertia::render('Users/Index', [
            'users' => \App\Models\User::with('roles')->get(),
        ]);
    }

    public function permissions(\App\Models\User $user)
    {
        \Illuminate\Support\Facades\Gate::authorize('users.manage');

        $currentUser = auth()->user();
        if ($user->isSuperAdmin() && (!$currentUser || !$currentUser->isSuperAdmin())) {
            abort(403, 'Unauthorized access to superadmin user permissions.');
        }

        return \Inertia\Inertia::render('Users/Permissions', [
            'user' => $user,
            'permissions' => \Spatie\Permission\Models\Permission::all(),
            'userPermissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    }

    public function updatePermissions(Request $request, \App\Models\User $user)
    {
        \Illuminate\Support\Facades\Gate::authorize('users.manage');

        $currentUser = auth()->user();
        if ($user->isSuperAdmin() && (!$currentUser || !$currentUser->isSuperAdmin())) {
            abort(403, 'Unauthorized modification of superadmin user permissions.');
        }

        $user->syncPermissions($request->permissions ?? []);

        return redirect()->route('users.index')->with('success', 'Permissions updated successfully');
    }

    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('users.manage');

        $currentUser = auth()->user();
        if (strtolower($request->role) === 'superadmin' && (!$currentUser || !$currentUser->isSuperAdmin())) {
            abort(403, 'Only Superadmins can assign the superadmin role.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|exists:roles,name',
        ]);

        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
        ]);

        $user->assignRole($request->role);

        return redirect()->back()->with('success', 'User created successfully');
    }

    public function update(Request $request, \App\Models\User $user)
    {
        \Illuminate\Support\Facades\Gate::authorize('users.manage');

        $currentUser = auth()->user();
        if ((strtolower($request->role) === 'superadmin' || $user->isSuperAdmin()) && (!$currentUser || !$currentUser->isSuperAdmin())) {
            abort(403, 'Only Superadmins can modify or assign the superadmin role.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|string|exists:roles,name',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if ($request->password) {
            $user->update(['password' => \Illuminate\Support\Facades\Hash::make($request->password)]);
        }

        $user->syncRoles([$request->role]);

        return redirect()->back()->with('success', 'User updated successfully');
    }

    public function destroy(\App\Models\User $user)
    {
        \Illuminate\Support\Facades\Gate::authorize('users.manage');
        
        $currentUser = auth()->user();
        if ($user->isSuperAdmin() && (!$currentUser || !$currentUser->isSuperAdmin())) {
            abort(403, 'Cannot delete superadmin user.');
        }

        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully');
    }
}
