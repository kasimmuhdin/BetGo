<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $totalListings = $user->properties()->count();
        $publishedListings = $user->properties()->where('status', 'published')->count();
        $draftListings = $user->properties()->where('status', 'draft')->count();
        $totalMedia = $user->media()->count();

        $recentListings = $user->properties()
            ->withCount('media')
            ->latest()
            ->take(5)
            ->get();


        $recentConversations = Conversation::query()
            ->forUser($user->id)
            ->with(['initiator', 'recipient', 'latestMessage', 'property'])
            ->orderByDesc('last_message_at')
            ->take(4)
            ->get();

        return view('dashboard.owner', compact(
            'totalListings',
            'publishedListings',
            'draftListings',
            'totalMedia',
            'recentListings',
            'recentConversations'
        ));
    }
}
