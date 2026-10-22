<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;

class ListingController extends Controller
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

    protected array $statuses = [
        'draft' => 'Draft',
        'pending' => 'Pending',
        'published' => 'Published',
        'rejected' => 'Rejected',
        'archived' => 'Archived',
    ];

    public function index(Request $request)
    {
        $query = Property::with('owner')->withCount('media');

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhereHas('owner', fn($o) => $o->where('name', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->input('property_type')) {
            $query->where('property_type', $type);
        }

        if ($listingType = $request->input('listing_type')) {
            $query->where('listing_type', $listingType);
        }

        $listings = $query->latest()->paginate(12)->withQueryString();

        $stats = [
            'total' => Property::count(),
            'published' => Property::where('status', 'published')->count(),
            'pending' => Property::where('status', 'pending')->count(),
            'rejected' => Property::where('status', 'rejected')->count(),
            'draft' => Property::where('status', 'draft')->count(),
        ];

        return view('admin.listings.all', [
            'listings' => $listings,
            'stats' => $stats,
            'propertyTypes' => $this->propertyTypes,
            'statuses' => $this->statuses,
        ]);
    }

    public function show(Property $listing)
    {
        $listing->load('media', 'owner', 'reviewer');

        return view('admin.listings.show', compact('listing'));
    }

    /**
     * Take a live listing off the site without deleting it — sets it to
     * 'archived' rather than 'draft' so it's clear this wasn't the owner's
     * choice, and keeps the review trail (reviewed_by/reviewed_at) intact.
     */
    public function unpublish(Request $request, Property $listing)
    {
        abort_unless($listing->status === 'published', 422, 'Only published listings can be unpublished.');

        $listing->update([
            'status' => 'archived',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return redirect()
            ->route('admin.listings.show', $listing)
            ->with('success', "\"{$listing->title}\" has been unpublished.");
    }
}
