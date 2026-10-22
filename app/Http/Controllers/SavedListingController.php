<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SavedListingController extends Controller
{

    public function index(Request $request)
    {
        $sort = $request->input('sort', 'recent');

        $query = $request->user()->savedProperties()->with('media');

        match ($sort) {
            'price_asc' => $query->orderBy('properties.price'),
            'price_desc' => $query->orderByDesc('properties.price'),
            default => $query->orderByPivot('created_at', 'desc')
        };

        $saved = $query->paginate(12)->withQueryString();

        return view('user.saved-listings.index', compact('saved', 'sort'));
    }
    public function store(Request $request, Property $property): JsonResponse
    {
        $user = $request->user();

        abort_if($property->user_id === $user->id, 422, 'You cant save your listings');

        $user->savedProperties()->syncWithoutDetaching($property->id);

        return response()->json(['saved' => true]);
    }

    public function destroy(Request $request, Property $property): JsonResponse
    {
        $request->user()->savedProperties()->detach($property->id);

        return response()->json(['saved' => false]);
    }
}
