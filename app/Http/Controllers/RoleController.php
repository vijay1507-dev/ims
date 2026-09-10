<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class RoleController extends Controller
{
    public function create()
    {
        Gate::authorize('users.manage');

        return Inertia::render('Administration/Roles/Create', [
            'permissions' => Permission::all(),
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('users.manage');

        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'array',
        ]);

        $role = Role::create(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('users.index', ['tab' => 'roles'])->with('success', 'Role created successfully');
    }

    public function permissions(Role $role)
    {
        Gate::authorize('users.manage');

        $currentUser = auth()->user();
        if (($role->id == 1 || strtolower($role->name) === 'superadmin') && (!$currentUser || !$currentUser->isSuperAdmin())) {
            abort(403, 'Unauthorized access to superadmin role.');
        }

        return Inertia::render('Administration/Roles/Permissions', [
            'role' => $role,
            'permissions' => Permission::all(),
            'rolePermissions' => $role->permissions->pluck('name'),
        ]);
    }

    public function updatePermissions(Request $request, Role $role)
    {
        Gate::authorize('users.manage');

        $currentUser = auth()->user();
        if (($role->id == 1 || strtolower($role->name) === 'superadmin') && (!$currentUser || !$currentUser->isSuperAdmin())) {
            abort(403, 'Unauthorized modification of superadmin role.');
        }

        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('users.index', ['tab' => 'roles'])->with('success', 'Role permissions updated successfully');
    }

    public function destroy(Role $role)
    {
        Gate::authorize('users.manage');
        
        if ($role->id == 1 || in_array(strtolower($role->name), ['superadmin', 'admin'])) {
            abort(403, 'Protected system role cannot be deleted.');
        }

        $role->delete();

        return redirect()->back()->with('success', 'Role deleted successfully');
    }
}
