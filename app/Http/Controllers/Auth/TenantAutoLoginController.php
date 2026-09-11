<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantAutoLoginController extends Controller
{
    /**
     * Authenticate the user into the tenant workspace session via temporary signed URL.
     */
    public function login(Request $request, User $user)
    {
        if (!tenancy()->initialized) {
            abort(404, 'Tenant workspace not initialized.');
        }

        if ((int)$user->tenant_id !== (int)tenancy()->tenant->id) {
            abort(403, 'Unauthorized. User does not belong to this tenant.');
        }

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return redirect('/')->with('success', 'Welcome to your workspace dashboard!');
    }
}
