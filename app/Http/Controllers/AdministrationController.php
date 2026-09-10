<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

class AdministrationController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('users.manage');

        $currentUser = auth()->user();

        $rolesQuery = Role::with('permissions');
        if (!$currentUser || !$currentUser->isSuperAdmin()) {
            $rolesQuery->where('id', '!=', 1)->where('name', '!=', 'superadmin');
        }

        return Inertia::render('Administration/Index', [
            'users' => User::with('roles')->get(),
            'roles' => $rolesQuery->get(),
        ]);
    }
}
