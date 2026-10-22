<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePropertyRequest;
use App\Http\Requests\UpdatePropertyRequest;
use App\Models\Media;
use App\Models\Property;
use App\Models\User;
use App\Notifications\ListingSubmittedForReview;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class ListingController extends Controller
{
    public function index()
    {
        $listings = auth()->user()->properties()
            ->withCount('media')
            ->latest()
            ->paginate(10);

        return view('owner.listings.index', compact('listings'));
    }

    public function create()
    {
        if (!auth()->user()->hasListingCredit()) {
            return redirect()->route('payments.pricing')
                ->with('warning', 'Please finish payment before posting a new listing.');
        }
        return view('owner.listings.create', [
            'listing' => new Property(['currency' => 'ETB', 'area_unit' => 'sqm']),
        ]);
    }

    public function store(StorePropertyRequest $request)
    {
        if (! $request->user()->hasListingCredit()) {
            return redirect()->route('payments.pricing')
                ->with('warning', 'Please finish payment before posting a new listing.');
        }
        $listing = DB::transaction(function () use ($request) {
            $listing = $request->user()->properties()->create([
                ...$request->safe()->except('photos', 'status'),
                ...$this->resolveStatus($request),

            ]);

            $this->syncMedia($listing, $request->input('media_ids', []));
            $this->applyCoverChoice($listing, $request->input('cover_photo_id'));

            return $listing;
        });
        if ($listing->status === 'pending') {
            Notification::send(User::where('role', 'admin')->get(), new ListingSubmittedForReview($listing));
        }

        return redirect()
            ->route('owner.listings.show', $listing)
            ->with('success',  $listing->status === 'pending'
                ? 'Listing submitted for review.'
                : 'Listing created successfully.');
    }

    public function show(Property $listing)
    {
        $this->authorizeOwner($listing);

        $listing->load('media');

        return view('owner.listings.show', compact('listing'));
    }

    public function edit(Property $listing)
    {
        $this->authorizeOwner($listing);

        $listing->load('media');

        return view('owner.listings.create', compact('listing'));
    }

    public function update(UpdatePropertyRequest $request, Property $listing)
    {
        $this->authorizeOwner($listing);

        DB::transaction(function () use ($request, $listing) {
            $data = $request->safe()->except('photos', 'status');

            $data = [...$data, ...$this->resolveStatus($request, $listing->status)];


            $listing->update($data);

            if ($deleteIds = $request->input('delete_photos', [])) {
                $photos = $listing->media()->whereIn('media.id', $deleteIds)->get();
                foreach ($photos as $photo) {
                    Storage::disk($photo->disk)->delete($photo->path);
                    $photo->delete();
                }
            }


            $this->syncMedia($listing, $request->input('media_ids', []));

            $this->applyCoverChoice($listing, $request->input('cover_photo_id'));
        });
        if ($listing->status === 'pending') {
            Notification::send(User::where('role', 'admin')->get(), new ListingSubmittedForReview($listing));
        }
        return redirect()
            ->route('owner.listings.show', $listing)
            ->with('success',  $listing->status === 'pending'
                ? 'Listing updated and re-submitted for review.'
                : 'Listing updated.');
    }

    public function destroy(Property $listing)
    {
        $this->authorizeOwner($listing);

        DB::transaction(function () use ($listing) {
            $listing->media()->detach();
            $listing->delete();
        });

        return redirect()
            ->route('owner.listings.index')
            ->with('success', 'Listing deleted.');
    }

    protected function syncMedia(Property $listing, array $mediaIds): void
    {
        $mediaIds = collect($mediaIds)->filter()->values();

        if ($listing->media()->exists()) {
            $listing->media()->detach(
                $listing->media()->whereNotIn('media.id', $mediaIds)->pluck('media.id')
            );
        }

        $owned = Media::whereIn('id', $mediaIds)
            ->where('user_id', auth()->id())
            ->pluck('id');

        $maxOrder = (int) DB::table('property_media')->where('property_id', $listing->id)->max('sort_order');
        $hasCover = DB::table('property_media')
            ->where('property_id', $listing->id)->where('is_cover', true)->exists();

        foreach ($owned->values() as $i => $id) {
            $listing->media()->syncWithoutDetaching([
                $id => [
                    'sort_order' => $maxOrder + $i + 1,
                    'is_cover'   => ! $hasCover && $i === 0,
                ],
            ]);
        }
    }

    protected function applyCoverChoice(Property $listing, ?int $coverId): void
    {
        if ($coverId) {
            $ownsPivotRow = DB::table('property_media')
                ->where('property_id', $listing->id)
                ->where('media_id', $coverId)
                ->exists();

            if ($ownsPivotRow) {
                DB::table('property_media')
                    ->where('property_id', $listing->id)
                    ->update(['is_cover' => false]);

                DB::table('property_media')
                    ->where('property_id', $listing->id)
                    ->where('media_id', $coverId)
                    ->update(['is_cover' => true]);

                return;
            }
        }

        $hasCover = DB::table('property_media')
            ->where('property_id', $listing->id)
            ->where('is_cover', true)
            ->exists();

        if ($hasCover) {
            return;
        }

        $fallbackId = DB::table('property_media')
            ->where('property_id', $listing->id)
            ->orderBy('sort_order')
            ->value('media_id');

        if ($fallbackId) {
            DB::table('property_media')
                ->where('property_id', $listing->id)
                ->where('media_id', $fallbackId)
                ->update(['is_cover' => true]);
        }
    }
    protected function resolveStatus(Request $request, ?string $currentStatus = null): array
    {
        $requested = $request->input('status', 'draft');
        $isAdmin = $request->user()->role === 'admin';

        if (! $isAdmin && $requested === 'published') {
            $requested = 'pending';
        }

        $data = ['status' => $requested];

        if ($requested === 'published') {
            $data['published_at'] = now();
        }
        if ($requested === 'pending' && $currentStatus !== 'pending') {
            $data['submitted_at'] = now();
        }

        if ($requested === 'pending' && $currentStatus === 'rejected') {
            $data['rejection_reason'] = null;
            $data['reviewed_by'] = null;
            $data['reviewed_at'] = null;
        }

        return $data;
    }
    protected function authorizeOwner(Property $listing): void
    {
        abort_unless($listing->user_id === auth()->id() || auth()->user()->role === 'admin', 403);
    }
}
