<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminDashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private AdminDashboardService $stats) {}

    public function index(Request $request)
    {
        $days = (int) $request->query('range', 30);
        if (! in_array($days, AdminDashboardService::RANGES, true)) {
            $days = 30;
        }

        // The refresh button clears the cache, then returns to a clean URL.
        if ($request->boolean('refresh')) {
            $this->stats->forget($days);

            return redirect()->route('admin.dashboard', ['range' => $days]);
        }

        return view('admin.dashboard', [
            'user' => $request->user(),
            'days' => $days,
            'ranges' => AdminDashboardService::RANGES,
            'overview' => $this->stats->overview($days),
            'attention' => $this->stats->attention(),
            'queue' => $this->stats->pendingListings(),
            'verifications' => $this->stats->pendingVerifications(),
            'payments' => $this->stats->latestPayments(),
            'activity' => $this->stats->activity(),
        ]);
    }
}
