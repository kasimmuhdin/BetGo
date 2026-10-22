@extends('layouts.app')
@section('title', 'Admin dashboard')

@section('content')
    @php
        use Carbon\CarbonInterface;

        $firstName = explode(' ', trim($user->name))[0];
        $hour = (int) now()->format('G');
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
        $updated = \Carbon\Carbon::parse($overview['generated_at']);
        $needs = collect($attention)->filter(fn($a) => $a['count'] > 0);
        $charts = $overview['charts'];
        $has = $overview['has'];
        $abs = ['syntax' => CarbonInterface::DIFF_ABSOLUTE];

        $toneBox = [
            'amber' => 'border-amber-500/30 bg-amber-500/5 hover:border-amber-400/60',
            'red' => 'border-red-500/30 bg-red-500/5 hover:border-red-400/60',
        ];
        $toneBadge = ['amber' => 'bg-amber-500/15 text-amber-300', 'red' => 'bg-red-500/15 text-red-300'];
        $statusBadge = [
            'success' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
            'pending' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
            'failed' => 'bg-red-500/10 text-red-400 border-red-500/20',
        ];
        $activityStyle = [
            'payment' => [
                'bg-emerald-500/15 text-emerald-400',
                'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            ],
            'listing' => [
                'bg-indigo-500/15 text-indigo-400',
                'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
            ],
            'verification' => ['bg-amber-500/15 text-amber-400', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
            'signup' => [
                'bg-cyan-500/15 text-cyan-400',
                'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z',
            ],
        ];
        $money = fn($p) => number_format($p->amount, $p->currency === 'USD' ? 2 : 0);
    @endphp

    <div class="mx-auto max-w-[1400px] space-y-6">

        {{-- ============ Header ============ --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-white">{{ $greeting }}, {{ $firstName }}</h1>
                <p class="mt-1 text-sm text-gray-400">{{ now()->format('l, F j') }} · here is what is happening on
                    {{ config('betgo.name', 'BetGo') }}.</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <div role="group" aria-label="Date range"
                    class="inline-flex rounded-lg border border-[#3a3a3d] bg-[#161414] p-0.5 text-sm">
                    @foreach ($ranges as $r)
                        <a href="{{ route('admin.dashboard', ['range' => $r]) }}"
                            @if ($r === $days) aria-current="true" @endif
                            class="rounded-md px-3.5 py-1.5 font-medium transition {{ $r === $days ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:text-white' }}">
                            {{ $r }} days
                        </a>
                    @endforeach
                </div>

                <a href="{{ route('admin.dashboard', ['range' => $days, 'refresh' => 1]) }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-[#3a3a3d] px-3 py-2 text-xs text-gray-400 transition hover:bg-[#2a2727] hover:text-white"
                    title="Reload the numbers now">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4 4v5h5M20 20v-5h-5M5.6 9A8 8 0 0118 7.3M18.4 15A8 8 0 016 16.7" />
                    </svg>
                    Updated {{ $updated->diffForHumans() }}
                </a>
            </div>
        </div>

        {{-- ============ Needs attention ============ --}}
        @if ($needs->isEmpty())
            <div class="flex items-center gap-3 rounded-xl border border-emerald-500/30 bg-emerald-500/5 px-5 py-4 text-sm text-emerald-300"
                role="status">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                You're all caught up. Nothing is waiting for review.
            </div>
        @else
            <section aria-labelledby="attention-title">
                <h2 id="attention-title" class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Needs
                    your attention</h2>
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($needs as $a)
                        <a href="{{ $a['url'] ?? '#' }}"
                            class="group flex items-center gap-4 rounded-xl border p-4 transition hover:-translate-y-0.5 {{ $toneBox[$a['tone']] }}">
                            <span
                                class="flex h-12 min-w-[3rem] items-center justify-center rounded-xl px-2 text-xl font-extrabold {{ $toneBadge[$a['tone']] }}">{{ number_format($a['count']) }}</span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-semibold text-white">{{ $a['label'] }}</span>
                                <span class="block truncate text-xs text-gray-400">{{ $a['hint'] }}</span>
                            </span>
                            <svg class="h-4 w-4 shrink-0 text-gray-500 transition group-hover:translate-x-0.5 group-hover:text-white"
                                fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ============ KPI cards ============ --}}
        <section aria-label="Key numbers" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-6">
            @foreach ($overview['kpis'] as $k)
                <div class="rounded-xl border border-[#3a3a3d] bg-gradient-to-br from-[#232020] to-[#1e1b1b] p-5">
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-400">{{ $k['label'] }}</p>
                    <p class="mt-2 text-3xl font-extrabold tracking-tight text-white">
                        {{ $k['value'] }}@if ($k['unit'])
                            <span class="ml-1 text-sm font-medium text-gray-500">{{ $k['unit'] }}</span>
                        @endif
                    </p>

                    <div class="mt-1.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs">
                        @if ($k['delta'])
                            <span @class([
                                'inline-flex items-center rounded-full px-2 py-0.5 font-semibold',
                                'bg-emerald-500/10 text-emerald-400' => $k['delta']['dir'] === 'up',
                                'bg-red-500/10 text-red-400' => $k['delta']['dir'] === 'down',
                                'bg-gray-500/10 text-gray-400' => $k['delta']['dir'] === 'flat',
                            ])>
                                {{ $k['delta']['dir'] === 'up' ? '↑' : ($k['delta']['dir'] === 'down' ? '↓' : '→') }}
                                {{ $k['delta']['label'] }}
                            </span>
                            <span class="text-gray-500">vs previous {{ $days }} days</span>
                        @endif
                    </div>
                    <p class="mt-1 text-xs text-gray-500">{{ $k['sub'] }}</p>

                    <div class="mt-3 h-9">
                        <x-admin.sparkline :points="$k['series']" :color="$k['color']" />
                    </div>
                </div>
            @endforeach
        </section>

        {{-- ============ Charts ============ --}}
        <div class="grid gap-6 xl:grid-cols-12">

            <x-admin.panel title="Revenue" :subtitle="'Daily revenue in ETB, last ' . $days . ' days'" class="xl:col-span-8">
                @if ($has['revenue'])
                    <div class="relative h-72">
                        <canvas id="chart-revenue" role="img"
                            aria-label="Daily revenue over the last {{ $days }} days. Total {{ number_format(array_sum($charts['revenue'])) }} ETB."></canvas>
                    </div>
                @else
                    <x-admin.empty message="No payments in this period yet." />
                @endif
            </x-admin.panel>

            <x-admin.panel title="Listings by status" subtitle="All listings on the platform" class="xl:col-span-4">
                @if ($has['status'])
                    <div class="relative h-72">
                        <canvas id="chart-status" role="img"
                            aria-label="Listings by status: {{ collect($charts['status']['labels'])->map(fn($l, $i) => $l . ' ' . $charts['status']['values'][$i])->implode(', ') }}."></canvas>
                    </div>
                @else
                    <x-admin.empty message="No listings yet." />
                @endif
            </x-admin.panel>

            <x-admin.panel title="Growth" :subtitle="'New users and new listings per day, last ' . $days . ' days'" class="xl:col-span-8">
                @if ($has['growth'])
                    <div class="relative h-72">
                        <canvas id="chart-growth" role="img"
                            aria-label="New users and new listings per day over the last {{ $days }} days."></canvas>
                    </div>
                @else
                    <x-admin.empty message="No new users or listings in this period." />
                @endif
            </x-admin.panel>

            <x-admin.panel title="Revenue by plan" subtitle="ETB, this period" class="xl:col-span-4">
                @if ($has['plans'])
                    <div class="relative h-72">
                        <canvas id="chart-plans" role="img"
                            aria-label="Revenue by plan: {{ collect($charts['plans']['labels'])->map(fn($l, $i) => $l . ' ' . number_format($charts['plans']['values'][$i]) . ' ETB')->implode(', ') }}."></canvas>
                    </div>
                @else
                    <x-admin.empty message="No plan sales in this period." />
                @endif
            </x-admin.panel>
        </div>

        {{-- ============ Work queues ============ --}}
        <div class="grid gap-6 xl:grid-cols-12">

            <x-admin.panel title="Listings waiting for review" subtitle="Oldest first" :flush="true"
                class="xl:col-span-8">
                <x-slot:action>
                    <a href="{{ route('admin.listings.pending') }}"
                        class="text-sm font-medium text-indigo-400 hover:text-indigo-300">View queue →</a>
                </x-slot:action>

                @if ($queue->isEmpty())
                    <x-admin.empty message="No listings are waiting for review." />
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <caption class="sr-only">Listings waiting for review</caption>
                            <thead class="bg-[#161414] text-xs uppercase text-gray-400">
                                <tr>
                                    <th scope="col" class="px-5 py-3 font-semibold">Listing</th>
                                    <th scope="col" class="px-5 py-3 font-semibold">Price</th>
                                    <th scope="col" class="px-5 py-3 font-semibold">Photos</th>
                                    <th scope="col" class="px-5 py-3 font-semibold">Waiting</th>
                                    <th scope="col" class="px-5 py-3"><span class="sr-only">Action</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#3a3a3d]">
                                @foreach ($queue as $p)
                                    @php
                                        $hours = $p->submitted_at
                                            ? (int) abs($p->submitted_at->diffInHours(now()))
                                            : null;
                                        $waitClass =
                                            $hours === null
                                                ? 'text-gray-400 bg-gray-800 border-[#3a3a3d]'
                                                : ($hours >= 48
                                                    ? 'text-red-400 bg-red-500/10 border-red-500/20'
                                                    : ($hours >= 24
                                                        ? 'text-amber-400 bg-amber-500/10 border-amber-500/20'
                                                        : 'text-gray-300 bg-gray-800 border-[#3a3a3d]'));
                                    @endphp
                                    <tr class="transition-colors hover:bg-[#2a2727]">
                                        <td class="px-5 py-3.5">
                                            <p class="max-w-[260px] truncate font-medium text-gray-100">
                                                {{ $p->title }}</p>
                                            <p class="text-xs text-gray-500">{{ $p->owner->name ?? 'Unknown' }}
                                                @if ($p->city)
                                                    · {{ $p->city }}
                                                @endif
                                            </p>
                                        </td>
                                        <td class="whitespace-nowrap px-5 py-3.5 font-medium text-white">
                                            {{ number_format($p->price) }} <span
                                                class="text-xs text-gray-500">{{ $p->currency }}</span></td>
                                        <td class="px-5 py-3.5 text-gray-400">{{ $p->media_count }}</td>
                                        <td class="px-5 py-3.5">
                                            <span
                                                class="whitespace-nowrap rounded-md border px-2 py-1 text-xs font-semibold {{ $waitClass }}">
                                                {{ $p->submitted_at ? $p->submitted_at->diffForHumans($abs) : '—' }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-3.5 text-right">
                                            <a href="{{ route('admin.listings.show', $p) }}"
                                                class="rounded-lg border border-indigo-600/30 bg-indigo-600/10 px-3 py-1.5 text-xs font-semibold text-indigo-400 transition hover:bg-indigo-600 hover:text-white">Review</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-admin.panel>

            <x-admin.panel title="Owner and agent requests" subtitle="Oldest first" :flush="true"
                class="xl:col-span-4">
                <x-slot:action>
                    <a href="{{ route('admin.users.all') }}"
                        class="text-sm font-medium text-indigo-400 hover:text-indigo-300">View all →</a>
                </x-slot:action>

                @if ($verifications->isEmpty())
                    <x-admin.empty message="No requests are waiting." />
                @else
                    <ul class="divide-y divide-[#3a3a3d]">
                        @foreach ($verifications as $v)
                            <li>
                                <a href="{{ route('admin.users.show', $v) }}"
                                    class="flex items-center gap-3 px-5 py-3.5 transition-colors hover:bg-[#2a2727]">
                                    <span
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-500/20 text-sm font-bold text-indigo-400">{{ strtoupper(mb_substr($v->name, 0, 1)) }}</span>
                                    <span class="min-w-0 flex-1">
                                        <span
                                            class="block truncate text-sm font-medium text-gray-100">{{ $v->name }}</span>
                                        <span class="block truncate text-xs text-gray-500">{{ $v->email }}</span>
                                    </span>
                                    <span class="shrink-0 text-right">
                                        <span @class([
                                            'block rounded-md border px-2 py-0.5 text-[11px] font-semibold',
                                            'border-indigo-500/20 bg-indigo-500/10 text-indigo-400' =>
                                                $v->role === 'owner',
                                            'border-cyan-500/20 bg-cyan-500/10 text-cyan-400' => $v->role === 'agent',
                                        ])>{{ ucfirst($v->role) }}</span>
                                        <span
                                            class="mt-1 block text-[11px] text-gray-500">{{ $v->updated_at->diffForHumans() }}</span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-admin.panel>

            <x-admin.panel title="Latest payments" :flush="true" class="xl:col-span-8">
                <x-slot:action>
                    <a href="{{ route('admin.payments.index') }}"
                        class="text-sm font-medium text-indigo-400 hover:text-indigo-300">All payments →</a>
                </x-slot:action>

                @if ($payments->isEmpty())
                    <x-admin.empty message="No payments yet." />
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <caption class="sr-only">Latest payments</caption>
                            <thead class="bg-[#161414] text-xs uppercase text-gray-400">
                                <tr>
                                    <th scope="col" class="px-5 py-3 font-semibold">Reference</th>
                                    <th scope="col" class="px-5 py-3 font-semibold">User</th>
                                    <th scope="col" class="px-5 py-3 font-semibold">Plan</th>
                                    <th scope="col" class="px-5 py-3 font-semibold">Amount</th>
                                    <th scope="col" class="px-5 py-3 font-semibold">Status</th>
                                    <th scope="col" class="px-5 py-3 font-semibold">When</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#3a3a3d]">
                                @foreach ($payments as $pay)
                                    <tr class="transition-colors hover:bg-[#2a2727]">
                                        <td class="px-5 py-3.5">
                                            <a href="{{ route('admin.payments.show', $pay) }}"
                                                class="font-mono text-xs text-indigo-400 hover:text-indigo-300">{{ Str::limit($pay->tx_ref, 18) }}</a>
                                            <span
                                                class="block text-[11px] capitalize text-gray-500">{{ $pay->provider }}</span>
                                        </td>
                                        <td class="px-5 py-3.5 text-gray-300">{{ $pay->user->name ?? 'Unknown' }}</td>
                                        <td class="px-5 py-3.5 text-gray-300">{{ $pay->plan->name ?? '—' }}</td>
                                        <td class="whitespace-nowrap px-5 py-3.5 font-medium text-white">
                                            {{ $money($pay) }} <span
                                                class="text-xs text-gray-500">{{ $pay->currency }}</span></td>
                                        <td class="px-5 py-3.5">
                                            <span
                                                class="rounded-md border px-2 py-1 text-xs font-semibold capitalize {{ $statusBadge[$pay->status] ?? '' }}">{{ $pay->status }}</span>
                                        </td>
                                        <td class="whitespace-nowrap px-5 py-3.5 text-xs text-gray-500">
                                            {{ $pay->created_at->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-admin.panel>

            <x-admin.panel title="Recent activity" class="xl:col-span-4">
                @if ($activity->isEmpty())
                    <x-admin.empty message="Nothing has happened yet." />
                @else
                    <ol class="space-y-4">
                        @foreach ($activity as $item)
                            @php [$box, $path] = $activityStyle[$item['type']]; @endphp
                            <li class="flex items-start gap-3">
                                <span
                                    class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $box }}">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" />
                                    </svg>
                                </span>
                                <div class="min-w-0 flex-1">
                                    @if ($item['url'])
                                        <a href="{{ $item['url'] }}"
                                            class="block break-words text-sm text-gray-200 hover:text-white">{{ $item['text'] }}</a>
                                    @else
                                        <p class="break-words text-sm text-gray-200">{{ $item['text'] }}</p>
                                    @endif
                                    <p class="text-xs text-gray-500">{{ $item['at']->diffForHumans() }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </x-admin.panel>
        </div>

        {{-- ============ Where and who ============ --}}
        <div class="grid gap-6 xl:grid-cols-12">
            <x-admin.panel title="Top areas" subtitle="Live listings by area" class="xl:col-span-6">
                @if ($has['areas'])
                    <div class="relative h-64">
                        <canvas id="chart-areas" role="img"
                            aria-label="Live listings by area: {{ collect($charts['areas']['labels'])->map(fn($l, $i) => $l . ' ' . $charts['areas']['values'][$i])->implode(', ') }}."></canvas>
                    </div>
                @else
                    <x-admin.empty message="No live listings yet." />
                @endif
            </x-admin.panel>

            <x-admin.panel title="Top owners and agents" subtitle="By live listings" :flush="true"
                class="xl:col-span-6">
                @if (empty($overview['sellers']))
                    <x-admin.empty message="No live listings yet." />
                @else
                    <ol class="divide-y divide-[#3a3a3d]">
                        @foreach ($overview['sellers'] as $i => $s)
                            <li>
                                <a href="{{ route('admin.users.show', $s['id']) }}"
                                    class="flex items-center gap-4 px-5 py-3.5 transition-colors hover:bg-[#2a2727]">
                                    <span
                                        class="w-5 text-center text-sm font-bold text-gray-500">{{ $i + 1 }}</span>
                                    <span
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-500/20 text-sm font-bold text-indigo-400">{{ strtoupper(mb_substr($s['name'], 0, 1)) }}</span>
                                    <span class="min-w-0 flex-1">
                                        <span
                                            class="block truncate text-sm font-medium text-gray-100">{{ $s['name'] }}</span>
                                        <span class="block text-xs capitalize text-gray-500">{{ $s['role'] }}</span>
                                    </span>
                                    <span class="text-sm font-semibold text-white">{{ $s['live'] }} <span
                                            class="text-xs font-normal text-gray-500">live</span></span>
                                </a>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </x-admin.panel>
        </div>

        <p class="pb-2 text-center text-xs text-gray-600">
            Totals and charts refresh every 5 minutes. The attention counts, lists and activity are always live.
        </p>
    </div>
@endsection

@push('scripts')
    <script type="application/json" id="dashboard-data">@json($charts, JSON_HEX_TAG | JSON_HEX_AMP)</script>
    @vite('resources/js/admin-dashboard.js')
@endpush
