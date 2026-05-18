<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        if ($request->user()->role !== 'admin') {
            $projects = $request->user()
                ->managedProjects()
                ->latest()
                ->take(5)
                ->get();
            $allProjects = $request->user()
                ->managedProjects()
                ->get();

            return view('dashboard', [
                'isAdmin' => false,
                'memberStats' => [
                    'total_projects' => $allProjects->count(),
                    'in_progress' => $allProjects->where('status', 'in_progress')->count(),
                    'completed' => $allProjects->where('status', 'completed')->count(),
                    'pending' => $allProjects->where('status', 'pending')->count(),
                ],
                'memberProjects' => $projects,
            ]);
        }

        return view('dashboard', [
            'isAdmin' => true,
            'stats' => [
                'total_users' => User::count(),
                'admins' => User::where('role', 'admin')->count(),
                'members' => User::where('role', 'member')->count(),
                'verified' => User::whereNotNull('email_verified_at')->count(),
                'created_today' => User::whereDate('created_at', today())->count(),
            ],
            'recentUsers' => User::latest()
                ->take(6)
                ->get(['id', 'name', 'email', 'role', 'created_at']),
        ]);
    }
}
