<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Gate;

class AdministrationController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('users.manage');

        return Inertia::render('Administration/Index', [
            'users' => User::with('roles')->get(),
            'roles' => \Spatie\Permission\Models\Role::with('permissions')->get(),
        ]);
    }
}
