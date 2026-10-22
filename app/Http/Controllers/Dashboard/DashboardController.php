<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Route: GET /dashboard  ->  name('dashboard')
     * Resolves to the right dashboard based on auth()->user()->role
     */
    public function index(Request $request)
    {
        $role = auth()->user()->role ?? 'user';

        return match ($role) {
            'admin' => $this->admin(),
            'owner' => $this->owner(),
            'agent' => $this->agent(),
            'user' => $this->users(),
            default => $this->users(),
        };
    }

    /* ------------------------------------------------------------------ */
    /*  ADMIN                                                              */
    /* ------------------------------------------------------------------ */
    public function admin()
    {
        $stats = [
            ['label' => 'Total Users',       'value' => '8,421',    'change' => '+4.2%', 'up' => true,  'icon' => 'users'],
            ['label' => 'Total Listings',    'value' => '2,340',    'change' => '+2.1%', 'up' => true,  'icon' => 'building'],
            ['label' => 'Total Bookings',    'value' => '1,189',    'change' => '+8.6%', 'up' => true,  'icon' => 'calendar'],
            ['label' => 'Platform Revenue',  'value' => '$184,320', 'change' => '-1.3%', 'up' => false, 'icon' => 'wallet'],
        ];

        $recentUsers = [
            ['name' => 'Sarah Kim',      'email' => 'sarah.kim@example.com',     'role' => 'Owner',  'joined' => '2 hours ago', 'status' => 'Active'],
            ['name' => 'Michael Reed',   'email' => 'michael.reed@example.com',  'role' => 'Agent',  'joined' => '5 hours ago', 'status' => 'Active'],
            ['name' => 'Jessica Tan',    'email' => 'jessica.tan@example.com',   'role' => 'Tenant', 'joined' => '1 day ago',   'status' => 'Pending'],
            ['name' => 'David Okafor',   'email' => 'david.okafor@example.com',  'role' => 'Owner',  'joined' => '2 days ago',  'status' => 'Active'],
            ['name' => 'Amara Bekele',   'email' => 'amara.bekele@example.com',  'role' => 'Agent',  'joined' => '3 days ago',  'status' => 'Suspended'],
        ];

        $pendingListings = [
            ['title' => 'Luxury 2BR Downtown',   'owner' => 'David Okafor', 'location' => 'NYC, Manhattan',     'price' => '$2,400/mo', 'submitted' => '3 hours ago'],
            ['title' => 'Spacious 4BR Villa',     'owner' => 'Sarah Kim',    'location' => 'LA, Beverly Hills',  'price' => '$4,800/mo', 'submitted' => '1 day ago'],
            ['title' => 'Cozy Studio in Soho',    'owner' => 'Michael Reed', 'location' => 'NYC, Soho',          'price' => '$1,650/mo', 'submitted' => '2 days ago'],
        ];

        $recentBookings = [
            ['tenant' => 'Alex Johnson', 'listing' => 'Luxury 2BR Downtown',  'date' => 'Sep 12, 2026', 'amount' => '$2,400', 'status' => 'Confirmed'],
            ['tenant' => 'Priya Shah',   'listing' => 'Cozy Studio in Soho',  'date' => 'Sep 10, 2026', 'amount' => '$1,650', 'status' => 'Confirmed'],
            ['tenant' => 'Liam Chen',    'listing' => 'Spacious 4BR Villa',   'date' => 'Sep 8, 2026',  'amount' => '$4,800', 'status' => 'Cancelled'],
        ];

        return view('dashboard.admin', compact('stats', 'recentUsers', 'pendingListings', 'recentBookings'));
    }

    /* ------------------------------------------------------------------ */
    /*  OWNER                                                              */
    /* ------------------------------------------------------------------ */
    public function owner()
    {
        $stats = [
            ['label' => 'My Properties',    'value' => '14',      'change' => '+2',    'up' => true,  'icon' => 'home'],
            ['label' => 'Active Bookings',  'value' => '9',       'change' => '+1',    'up' => true,  'icon' => 'calendar'],
            ['label' => 'Monthly Revenue',  'value' => '$18,240', 'change' => '+6.4%', 'up' => true,  'icon' => 'wallet'],
            ['label' => 'Occupancy Rate',   'value' => '86%',     'change' => '-3.0%', 'up' => false, 'icon' => 'trending'],
        ];

        $myListings = [
            ['title' => 'Luxury 2BR Downtown', 'location' => 'NYC, Manhattan',    'price' => '$2,400/mo', 'status' => 'Occupied',  'views' => 342],
            ['title' => 'Spacious 4BR Villa',   'location' => 'LA, Beverly Hills', 'price' => '$4,800/mo', 'status' => 'Vacant',    'views' => 210],
            ['title' => 'Cozy Studio in Soho',  'location' => 'NYC, Soho',         'price' => '$1,650/mo', 'status' => 'Occupied',  'views' => 128],
            ['title' => 'Riverside 3BR Loft',   'location' => 'Chicago, IL',       'price' => '$3,100/mo', 'status' => 'Pending',   'views' => 76],
        ];

        $recentBookings = [
            ['tenant' => 'Alex Johnson', 'listing' => 'Luxury 2BR Downtown', 'checkin' => 'Sep 12, 2026', 'amount' => '$2,400', 'status' => 'Confirmed'],
            ['tenant' => 'Priya Shah',   'listing' => 'Cozy Studio in Soho', 'checkin' => 'Sep 10, 2026', 'amount' => '$1,650', 'status' => 'Confirmed'],
        ];

        $recentMessages = [
            ['name' => 'Alex Johnson', 'preview' => 'Is the parking spot included in the rent?', 'time' => '10m ago'],
            ['name' => 'Priya Shah',   'preview' => 'Thank you, just signed the lease!',          'time' => '2h ago'],
            ['name' => 'Liam Chen',    'preview' => 'Can I reschedule the viewing to Friday?',     'time' => '1d ago'],
        ];

        return view('dashboard.owner', compact('stats', 'myListings', 'recentBookings', 'recentMessages'));
    }
    public function users()
    {
        $stats = [
            ['label' => 'Saved Listings',   'value' => '12',    'change' => '+2',    'up' => true,  'icon' => 'heart'],

        ];
        return view('dashboard.users', compact('stats'));
    }
    /* ------------------------------------------------------------------ */
    /*  AGENT                                                              */
    /* ------------------------------------------------------------------ */
    public function agent()
    {
        $stats = [
            ['label' => 'Assigned Listings',   'value' => '23',    'change' => '+3',    'up' => true,  'icon' => 'building'],
            ['label' => 'Scheduled Viewings',  'value' => '7',     'change' => '+2',    'up' => true,  'icon' => 'clock'],
            ['label' => 'Closed Deals',        'value' => '5',     'change' => '+1',    'up' => true,  'icon' => 'check'],
            ['label' => 'Commission Earned',   'value' => '$3,120', 'change' => '+11.5%', 'up' => true,  'icon' => 'wallet'],
        ];

        $upcomingViewings = [
            ['listing' => 'Luxury 2BR Downtown', 'client' => 'Alex Johnson', 'date' => 'Sep 8, 2026',  'time' => '10:00 AM', 'status' => 'Confirmed'],
            ['listing' => 'Cozy Studio in Soho',  'client' => 'Priya Shah',   'date' => 'Sep 8, 2026',  'time' => '2:30 PM',  'status' => 'Confirmed'],
            ['listing' => 'Riverside 3BR Loft',   'client' => 'Liam Chen',    'date' => 'Sep 9, 2026',  'time' => '11:00 AM', 'status' => 'Pending'],
        ];

        $myListings = [
            ['title' => 'Luxury 2BR Downtown', 'location' => 'NYC, Manhattan',    'price' => '$2,400/mo', 'status' => 'Active'],
            ['title' => 'Cozy Studio in Soho',  'location' => 'NYC, Soho',         'price' => '$1,650/mo', 'status' => 'Active'],
            ['title' => 'Riverside 3BR Loft',   'location' => 'Chicago, IL',       'price' => '$3,100/mo', 'status' => 'Under Offer'],
        ];

        $leads = [
            ['name' => 'Nathan Brooks', 'interest' => 'Luxury 2BR Downtown', 'time' => '25m ago'],
            ['name' => 'Grace Osei',    'interest' => 'Riverside 3BR Loft',  'time' => '3h ago'],
            ['name' => 'Tom Bekele',    'interest' => 'Cozy Studio in Soho', 'time' => '1d ago'],
        ];

        return view('dashboard.agent', compact('stats', 'upcomingViewings', 'myListings', 'leads'));
    }
}
