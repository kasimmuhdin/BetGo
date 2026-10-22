<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $totalListings = $user->properties()->count();
        $activeListings = $user->properties()->where('status', 'published')->count();

        $scheduledViewings = 7;
        $commissionEarned = 3120;

        $assignedListings = $user->properties()
            ->withCount('media')
            ->latest()
            ->take(5)
            ->get();

        $recentLeads = $user->conversationsReceived()
            ->with(['initiator'])
            ->latest()
            ->take(4)
            ->get();

        return view('dashboard.agent', compact(
            'totalListings',
            'activeListings',
            'scheduledViewings',
            'commissionEarned',
            'assignedListings',
            'recentLeads'
        ));
    }
}
