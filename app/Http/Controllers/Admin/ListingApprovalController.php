<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Notifications\ListingReviewed;
use Illuminate\Http\Request;

class ListingApprovalController extends Controller
{
    protected array $propertyTypes = [
        'apartment' => 'Apartment',
        'house' => 'House',
        'villa' => 'Villa',
        'office' => 'Office',
        'land' => 'Land',
        'commercial' => 'Commercial',
        'warehouse' => 'Warehouse',
    ];

    public function index(Request $request)
    {
        $query = Property::pending()->with('owner')->withCount('media');

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhereHas('owner', fn($o) => $o->where('name', 'like', "%{$search}%"));
            });
        }

        if ($type = $request->input('property_type')) {
            $query->where('property_type', $type);
        }

        if ($listingType = $request->input('listing_type')) {
            $query->where('listing_type', $listingType);
        }

        $listings = $query->oldest('submitted_at')->paginate(10)->withQueryString();

        $pending = Property::pending()->get(['id', 'submitted_at']);

        $stats = [
            'pending_count' => $pending->count(),
            'approved_today' => Property::where('status', 'published')->whereDate('reviewed_at', today())->count(),
            'rejected_today' => Property::where('status', 'rejected')->whereDate('reviewed_at', today())->count(),
            'avg_wait_hours' => $pending->isNotEmpty()
                ? round($pending->avg(fn($p) => $p->submitted_at ? now()->diffInHours($p->submitted_at) : 0)) : 0,
            'oldest_wait_hours' => $pending->isNotEmpty()
                ? round($pending->max(fn($p) => $p->submitted_at ? now()->diffInHours($p->submitted_at) : 0)) : 0,
        ];

        return view('admin.listings.index', [
            'listings' => $listings,
            'stats' => $stats,
            'propertyTypes' => $this->propertyTypes,
        ]);
    }

    public function approve(Request $request, Property $listing)
    {
        abort_unless($listing->status === 'pending', 422, 'Only pending listings can be approved.');

        $listing->update([
            'status' => 'published',
            'published_at' => $listing->published_at ?? now(),
            'is_verified' => true,
            'rejection_reason' => null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $listing->owner->notify(new ListingReviewed($listing));

        return redirect()->route('admin.listings.pending')->with('success', "\"{$listing->title}\" approved and published.");
    }

    public function reject(Request $request, Property $listing)
    {
        abort_unless($listing->status === 'pending', 422, 'Only pending listings can be rejected.');

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $listing->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $listing->owner->notify(new ListingReviewed($listing));

        return redirect()->route('admin.listings.pending')->with('success', "\"{$listing->title}\" rejected.");
    }
}
