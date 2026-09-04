<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    /**
     * Display configuration layout with dynamically fetched user role collections.
     */
    public function index(): Response
    {
        \Illuminate\Support\Facades\Gate::authorize('settings.manage');

        // Fetch baseline system accounts to drive active user management matrices
        $dbUsers = User::all();
        
        $usersCollection = $dbUsers->map(function ($u, $idx) {
            $roles = ['Administrator', 'Manager', 'Support Agent', 'Viewer'];
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $idx === 0 ? 'Administrator' : ($idx === 1 ? 'Manager' : 'Support Agent'),
                'avatar' => 'https://picsum.photos/seed/user' . ($u->id + 10) . '/32/32.jpg',
                'last_active' => $idx === 0 ? '2 minutes ago' : '3 hours ago',
                'status' => 'Active',
            ];
        });

        // Provide robust default set if no users are seeded in baseline DB
        if ($usersCollection->isEmpty()) {
            $usersCollection = collect([
                [
                    'id' => 1,
                    'name' => 'John Doe',
                    'email' => 'john@saasmanager.com',
                    'role' => 'Administrator',
                    'avatar' => 'https://picsum.photos/seed/user1/32/32.jpg',
                    'last_active' => '2 hours ago',
                    'status' => 'Active',
                ],
                [
                    'id' => 2,
                    'name' => 'Sarah Smith',
                    'email' => 'sarah@saasmanager.com',
                    'role' => 'Manager',
                    'avatar' => 'https://picsum.photos/seed/user2/32/32.jpg',
                    'last_active' => '1 day ago',
                    'status' => 'Active',
                ],
                [
                    'id' => 3,
                    'name' => 'Michael Chen',
                    'email' => 'michael@saasmanager.com',
                    'role' => 'Support Agent',
                    'avatar' => 'https://picsum.photos/seed/user3/32/32.jpg',
                    'last_active' => '3 days ago',
                    'status' => 'Active',
                ],
            ]);
        }

        return Inertia::render('Settings/Index', [
            'users' => $usersCollection,
            'config' => [
                'company_name' => 'SaaS Manager Inc.',
                'company_email' => 'support@saasmanager.com',
                'phone' => '+1 (555) 123-4567',
                'website' => 'https://saasmanager.com',
                'currency' => 'USD - US Dollar',
                'timezone' => 'UTC-5:00 Eastern Time',
            ]
        ]);
    }
}
