<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\Plan;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $sort = $this->sort($request);

        $query = Property::query()
            ->published()
            ->filter($filters)
            ->with(['media' => fn($q) => $q->orderByPivot('sort_order')]);

        match ($sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            default => $query->latest('published_at'),
        };

        // Tie-breaker keeps pagination stable when values are equal.
        $properties = $query->orderByDesc('id')->paginate(9)->withQueryString();

        return view('home.homes', [
            'properties'    => $properties,
            'filters'       => $filters,
            'sort'          => $sort,
            'propertyTypes' => Property::TYPES,
            'savedIds'      => $this->savedIds($properties->pluck('id')),
            'typeCounts'    => $this->typeCounts(),
            'popularAreas'  => $this->popularAreas(),
            'startingPrice' => Plan::publicLists()->min('price'),
            'faqs' => Faq::published()->ordered()->limit(5)->get(),
        ]);
    }

    public function show(Property $property)
    {
        $user = auth()->user();

        abort_unless(
            $property->isPublic() || ($user && ($user->id === $property->user_id || $user->role === 'admin')),
            404
        );

        $property->load(['owner', 'media' => fn($q) => $q->orderByPivot('sort_order')]);

        $relatedProperties = Property::query()
            ->where('status', 'published')
            ->where('id', '!=', $property->id)
            ->where(function ($q) use ($property) {
                $q->where('property_type', $property->property_type)
                    ->orWhere('city', $property->city);
            })
            ->with(['media' => fn($q) => $q->orderByPivot('sort_order')])
            ->latest('published_at')
            ->limit(3)
            ->get();

        $isSaved = $user
            ? $user->savedProperties()->where('properties.id', $property->id)->exists()
            : false;

        $savedIds = $this->savedIds($relatedProperties->pluck('id'));

        return view('home.properties.show', compact('property', 'relatedProperties', 'isSaved', 'savedIds'));
    }

    /** Public GET input is untrusted: keep only values we understand. */
    protected function filters(Request $request): array
    {
        $text = fn(string $key) => is_string($v = $request->input($key)) ? trim($v) : '';

        $q = Str::limit($text('q'), 100, '');
        $listingType = $text('listing_type');
        $propertyType = $text('property_type');
        $min = $text('min_price');
        $max = $text('max_price');
        $beds = (int) $text('bedrooms');

        return array_filter([
            'q' => $q !== '' ? $q : null,
            'listing_type' => in_array($listingType, ['rent', 'sale'], true) ? $listingType : null,
            'property_type' => array_key_exists($propertyType, Property::TYPES) ? $propertyType : null,
            'min_price' => is_numeric($min) && (float) $min > 0 ? (float) $min : null,
            'max_price' => is_numeric($max) && (float) $max > 0 ? (float) $max : null,
            'bedrooms' => $beds >= 1 && $beds <= 10 ? $beds : null,
        ], fn($v) => $v !== null);
    }

    protected function sort(Request $request): string
    {
        $sort = $request->input('sort');

        return in_array($sort, ['price_asc', 'price_desc'], true) ? $sort : 'newest';
    }

    /** One query for the whole page, not one per card. */
    protected function savedIds($ids): array
    {
        if (! auth()->check() || $ids->isEmpty()) {
            return [];
        }

        return auth()->user()->savedProperties()
            ->whereIn('properties.id', $ids)
            ->pluck('properties.id')
            ->all();
    }

    protected function typeCounts(): array
    {
        return Cache::remember('home.type_counts', 300, fn() => Property::published()
            ->select('property_type', DB::raw('COUNT(*) as total'))
            ->groupBy('property_type')
            ->pluck('total', 'property_type')
            ->all());
    }

    protected function popularAreas(): array
    {
        return Cache::remember('home.popular_areas', 300, fn() => Property::published()
            ->whereNotNull('sub_city')->where('sub_city', '!=', '')
            ->select('sub_city', DB::raw('COUNT(*) as total'))
            ->groupBy('sub_city')
            ->orderByDesc('total')
            ->limit(8)
            ->pluck('sub_city')
            ->all());
    }
}
