<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use App\Models\User;

class DashboardController extends Controller
{
    public function index(): Response
    {
        /** @var User $user */
        $user = auth()->user();

        return Inertia::render('Dashboard', [
            'userRole' => $user->role,
            'stats'    => [
                'total'    => \App\Models\Loan::count(),
                'pending'  => \App\Models\Loan::where('status', 'pending')->count(),
                'approved' => \App\Models\Loan::where('status', 'approved')->count(),
            ],
        ]);
    }
}