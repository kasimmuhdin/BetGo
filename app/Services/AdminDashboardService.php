<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Property;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class AdminDashboardService
{
    public const RANGES = [7, 30, 90];

    private const CACHE_SECONDS = 300;

    private const STATUS_META = [
        'published' => ['Published', '#3ddc97'],
        'pending' => ['Pending', '#fbbf24'],
        'draft' => ['Draft', '#64748b'],
        'rejected' => ['Rejected', '#ff6b6b'],
        'archived' => ['Archived', '#a78bfa'],
    ];

    /* ------------------------------------------------------------------
     |  Cached: totals, comparisons and chart data
     | ------------------------------------------------------------------ */

    public function overview(int $days): array
    {
        return Cache::remember($this->key($days), self::CACHE_SECONDS, fn() => $this->compute($days));
    }

    public function forget(int $days): void
    {
        Cache::forget($this->key($days));
    }

    private function key(int $days): string
    {
        return "admin.dashboard.overview.v1.{$days}";
    }

    private function compute(int $days): array
    {
        $end = CarbonImmutable::now();
        $start = $end->subDays($days - 1)->startOfDay();
        $current = [$start, $end];
        $previous = [$start->subDays($days), $start->subSecond()];

        // One grouped query per series, zero-filled so empty days still appear.
        $revenueSeries = $this->series(
            Payment::query()->where('status', 'success')->where('currency', 'ETB'),
            'paid_at',
            $start,
            $end,
            'SUM(amount)'
        );
        $userSeries = $this->series(User::query(), 'created_at', $start, $end);
        $listingSeries = $this->series(Property::query(), 'created_at', $start, $end);

        $revenue = array_sum($revenueSeries);
        $revenuePrev = (float) Payment::query()->where('status', 'success')->where('currency', 'ETB')
            ->whereBetween('paid_at', $previous)->sum('amount');
        $usd = (float) Payment::query()->where('status', 'success')->where('currency', 'USD')
            ->whereBetween('paid_at', $current)->sum('amount');
        $paymentCount = Payment::query()->where('status', 'success')->whereBetween('paid_at', $current)->count();

        $newUsers = (int) array_sum($userSeries);
        $newUsersPrev = User::query()->whereBetween('created_at', $previous)->count();
        $newListings = (int) array_sum($listingSeries);
        $newListingsPrev = Property::query()->whereBetween('created_at', $previous)->count();

        // Moderation quality: only listings an admin decided on during this period.
        $reviews = Property::query()
            ->whereIn('status', ['published', 'rejected'])
            ->whereNotNull('reviewed_at')->whereNotNull('submitted_at')
            ->whereBetween('reviewed_at', $current)
            ->limit(5000)
            ->get(['status', 'submitted_at', 'reviewed_at']);

        $approved = $reviews->where('status', 'published')->count();
        $rejected = $reviews->where('status', 'rejected')->count();
        $rate = ($approved + $rejected) > 0 ? $approved / ($approved + $rejected) * 100 : null;
        $median = $this->median($reviews->map(fn($p) => abs($p->submitted_at->diffInMinutes($p->reviewed_at))));
        [$medianValue, $medianUnit] = $this->duration($median);

        $statusCounts = Property::query()
            ->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $live = Property::published()->count();
        $totalUsers = User::count();

        $plans = Payment::query()
            ->join('plans', 'plans.id', '=', 'payments.plan_id')
            ->where('payments.status', 'success')->where('payments.currency', 'ETB')
            ->whereBetween('payments.paid_at', $current)
            ->selectRaw('plans.name as plan, SUM(payments.amount) as total')
            ->groupBy('plans.id', 'plans.name')->orderByDesc('total')->get();

        $areas = Property::published()
            ->selectRaw("COALESCE(NULLIF(sub_city, ''), city) as area, COUNT(*) as total")
            ->groupBy('area')->orderByDesc('total')->limit(6)->get()
            ->filter(fn($r) => filled($r->area));

        $sellers = User::query()
            ->whereIn('role', ['owner', 'agent'])
            ->withCount(['properties as live_count' => fn($q) => $q->published()])
            ->orderByDesc('live_count')->limit(5)->get()
            ->filter(fn($u) => $u->live_count > 0)
            ->map(fn($u) => ['id' => $u->id, 'name' => $u->name, 'role' => $u->role, 'live' => (int) $u->live_count])
            ->values()->all();

        $status = collect(self::STATUS_META)
            ->map(fn($meta, $key) => ['label' => $meta[0], 'color' => $meta[1], 'value' => (int) ($statusCounts[$key] ?? 0)])
            ->filter(fn($s) => $s['value'] > 0)->values();

        $growthTotal = $newUsers + $newListings;

        return [
            'generated_at' => now()->toIso8601String(),
            'days' => $days,

            'kpis' => [
                [
                    'label' => 'Revenue',
                    'value' => number_format($revenue),
                    'unit' => 'ETB',
                    'delta' => $this->delta($revenue, $revenuePrev),
                    'sub' => number_format($paymentCount) . ' ' . Str::plural('payment', $paymentCount)
                        . ($usd > 0 ? ' · $' . number_format($usd, 2) . ' by card' : ''),
                    'series' => array_values($revenueSeries),
                    'color' => '#00c8ff',
                ],
                [
                    'label' => 'New users',
                    'value' => number_format($newUsers),
                    'unit' => '',
                    'delta' => $this->delta($newUsers, $newUsersPrev),
                    'sub' => number_format($totalUsers) . ' in total',
                    'series' => array_values($userSeries),
                    'color' => '#818cf8',
                ],
                [
                    'label' => 'New listings',
                    'value' => number_format($newListings),
                    'unit' => '',
                    'delta' => $this->delta($newListings, $newListingsPrev),
                    'sub' => number_format($live) . ' live now',
                    'series' => array_values($listingSeries),
                    'color' => '#fbbf24',
                ],
                [
                    'label' => 'Live listings',
                    'value' => number_format($live),
                    'unit' => '',
                    'delta' => null,
                    'sub' => number_format((int) ($statusCounts['pending'] ?? 0)) . ' waiting for review',
                    'series' => [],
                    'color' => '#3ddc97',
                ],
                [
                    'label' => 'Approval rate',
                    'value' => $rate === null ? '—' : (string) round($rate),
                    'unit' => $rate === null ? '' : '%',
                    'delta' => null,
                    'sub' => "{$approved} approved · {$rejected} rejected",
                    'series' => [],
                    'color' => '#3ddc97',
                ],
                [
                    'label' => 'Median review time',
                    'value' => $medianValue,
                    'unit' => $medianUnit,
                    'delta' => null,
                    'sub' => $reviews->isEmpty() ? 'No reviews in this period' : 'from ' . $reviews->count() . ' reviews',
                    'series' => [],
                    'color' => '#818cf8',
                ],
            ],

            'charts' => [
                'labels' => array_map(fn($d) => CarbonImmutable::parse($d)->format('M j'), array_keys($revenueSeries)),
                'revenue' => array_values($revenueSeries),
                'users' => array_values($userSeries),
                'listings' => array_values($listingSeries),
                'status' => [
                    'labels' => $status->pluck('label')->all(),
                    'values' => $status->pluck('value')->all(),
                    'colors' => $status->pluck('color')->all(),
                ],
                'plans' => ['labels' => $plans->pluck('plan')->all(), 'values' => $plans->pluck('total')->map(fn($v) => (float) $v)->all()],
                'areas' => ['labels' => $areas->pluck('area')->values()->all(), 'values' => $areas->pluck('total')->map(fn($v) => (int) $v)->values()->all()],
            ],

            'has' => [
                'revenue' => $revenue > 0,
                'growth' => $growthTotal > 0,
                'status' => $status->isNotEmpty(),
                'plans' => $plans->isNotEmpty(),
                'areas' => $areas->isNotEmpty(),
            ],

            'sellers' => $sellers,
        ];
    }

    /* ------------------------------------------------------------------
     |  Live: always fresh
     | ------------------------------------------------------------------ */

    public function attention(): array
    {
        $parse = fn($v) => $v ? CarbonImmutable::parse($v) : null;
        $waiting = fn(?CarbonImmutable $d) => $d ? 'Oldest waiting ' . $d->diffForHumans(['syntax' => CarbonInterface::DIFF_ABSOLUTE]) : 'Nothing waiting';
        $tone = fn(?CarbonImmutable $d) => $d && $d->lt(now()->subHours(48)) ? 'red' : 'amber';

        $listings = Property::query()->where('status', 'pending')
            ->selectRaw('COUNT(*) as total, MIN(submitted_at) as oldest')->first();
        $users = User::pendingVerification()
            ->selectRaw('COUNT(*) as total, MIN(updated_at) as oldest')->first();
        $stuck = Payment::query()->where('status', 'pending')->where('created_at', '<', now()->subMinutes(30))
            ->selectRaw('COUNT(*) as total')->first();
        $failed = Payment::query()->where('status', 'failed')->where('created_at', '>=', now()->subDay())->count();

        $listingsOldest = $parse($listings->oldest);
        $usersOldest = $parse($users->oldest);

        return [
            [
                'key' => 'listings',
                'label' => 'Listings to review',
                'count' => (int) $listings->total,
                'hint' => $waiting($listingsOldest),
                'tone' => $tone($listingsOldest),
                'url' => $this->url('admin.listings.pending')
            ],
            [
                'key' => 'users',
                'label' => 'Owner and agent requests',
                'count' => (int) $users->total,
                'hint' => $waiting($usersOldest),
                'tone' => $tone($usersOldest),
                'url' => $this->url('admin.users.index')
            ],
            [
                'key' => 'stuck',
                'label' => 'Payments stuck pending',
                'count' => (int) $stuck->total,
                'hint' => 'Started over 30 minutes ago',
                'tone' => 'amber',
                'url' => $this->url('admin.payments.index', ['status' => 'pending'])
            ],
            [
                'key' => 'failed',
                'label' => 'Failed payments today',
                'count' => $failed,
                'hint' => 'Last 24 hours',
                'tone' => 'red',
                'url' => $this->url('admin.payments.index', ['status' => 'failed'])
            ],
        ];
    }

    public function pendingListings(int $limit = 5): Collection
    {
        return Property::query()->where('status', 'pending')
            ->with('owner:id,name')->withCount('media')
            ->orderByRaw('submitted_at IS NULL, submitted_at')
            ->limit($limit)->get();
    }

    public function pendingVerifications(int $limit = 5): Collection
    {
        return User::pendingVerification()->orderBy('updated_at')->limit($limit)
            ->get(['id', 'name', 'email', 'role', 'updated_at']);
    }

    public function latestPayments(int $limit = 6): Collection
    {
        return Payment::with(['user:id,name', 'plan:id,name'])->latest()->limit($limit)->get();
    }

    public function activity(int $limit = 10): Collection
    {
        $items = collect();

        Payment::with(['user:id,name', 'plan:id,name'])->where('status', 'success')
            ->latest('paid_at')->limit(6)->get()
            ->each(fn($p) => $items->push([
                'type' => 'payment',
                'at' => $p->paid_at ?? $p->created_at,
                'text' => ($p->user->name ?? 'Someone') . ' paid ' . number_format($p->amount, $p->currency === 'USD' ? 2 : 0)
                    . ' ' . $p->currency . ' for ' . ($p->plan->name ?? 'a plan'),
                'url' => $this->url('admin.payments.show', ['payment' => $p->id]),
            ]));

        Property::with('owner:id,name')->whereNotNull('submitted_at')
            ->latest('submitted_at')->limit(6)->get()
            ->each(fn($p) => $items->push([
                'type' => 'listing',
                'at' => $p->submitted_at,
                'text' => ($p->owner->name ?? 'Someone') . ' submitted “' . Str::limit($p->title, 40) . '” for review',
                'url' => $this->url('admin.listings.show', ['listing' => $p->id]),
            ]));

        User::pendingVerification()->latest('updated_at')->limit(6)->get()
            ->each(fn($u) => $items->push([
                'type' => 'verification',
                'at' => $u->updated_at,
                'text' => $u->name . ' asked to become ' . ($u->role === 'agent' ? 'an agent' : 'an owner'),
                'url' => $this->url('admin.users.show', ['user' => $u->id]),
            ]));

        User::where('role', 'user')->latest()->limit(6)->get()
            ->each(fn($u) => $items->push([
                'type' => 'signup',
                'at' => $u->created_at,
                'text' => $u->name . ' joined',
                'url' => null,
            ]));

        return $items->sortByDesc(fn($i) => $i['at'])->take($limit)->values();
    }

    /* ------------------------------------------------------------------
     |  Helpers
     | ------------------------------------------------------------------ */

    /** One GROUP BY query for the whole range; days with no rows are filled with 0. */
    private function series($query, string $column, CarbonImmutable $from, CarbonImmutable $to, string $aggregate = 'COUNT(*)'): array
    {
        $rows = $query->whereBetween($column, [$from, $to])
            ->selectRaw("DATE({$column}) as day, {$aggregate} as total")
            ->groupBy('day')
            ->pluck('total', 'day');

        $out = [];
        for ($d = $from->startOfDay(); $d <= $to; $d = $d->addDay()) {
            $out[$d->toDateString()] = (float) ($rows[$d->toDateString()] ?? 0);
        }

        return $out;
    }

    private function delta(float|int $current, float|int $previous): ?array
    {
        if ($previous == 0) {
            return $current > 0 ? ['dir' => 'up', 'label' => 'New'] : null;
        }

        $pct = ($current - $previous) / $previous * 100;

        if (abs($pct) < 0.05) {
            return ['dir' => 'flat', 'label' => '0%'];
        }

        return ['dir' => $pct > 0 ? 'up' : 'down', 'label' => sprintf('%+.1f%%', $pct)];
    }

    private function median(Collection $minutes): ?float
    {
        $sorted = $minutes->sort()->values();
        $n = $sorted->count();

        if ($n === 0) {
            return null;
        }

        return $n % 2 ? (float) $sorted[intdiv($n, 2)] : ($sorted[$n / 2 - 1] + $sorted[$n / 2]) / 2;
    }

    private function duration(?float $minutes): array
    {
        if ($minutes === null) {
            return ['—', ''];
        }
        if ($minutes < 60) {
            return [(string) max(1, round($minutes)), 'min'];
        }

        $hours = $minutes / 60;
        $trim = fn($n) => rtrim(rtrim(number_format($n, 1), '0'), '.');

        return $hours < 48 ? [$trim($hours), 'hours'] : [$trim($hours / 24), 'days'];
    }

    private function url(string $name, array $params = []): ?string
    {
        return Route::has($name) ? route($name, $params) : null;
    }
}
