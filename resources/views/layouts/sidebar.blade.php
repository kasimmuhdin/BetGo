@php use App\Support\DashboardNav as Nav; @endphp

<aside id="sidebar" aria-label="Sidebar" :class="drawer ? 'translate-x-0' : '-translate-x-full'"
    :inert="mobile && !drawer"
    class="sb-aside fixed inset-y-0 left-0 z-40 flex w-64 shrink-0 flex-col border-r border-slate-800 bg-slate-900 shadow-xl transition-transform duration-200 ease-in-out md:static md:translate-x-0 md:transition-[width]">

    {{-- Brand --}}
    <a href="{{ $nav['links']['home'] }}" aria-label="{{ config('betgo.name', 'BetGo') }} website"
        class="sb-center flex h-[60px] shrink-0 items-center gap-3 border-b border-slate-800 px-4">
        <img src="{{ asset('logo.svg') }}" alt="" width="36" height="36"
            class="h-9 w-9 shrink-0 rounded-lg object-cover">
        <span class="sb-hide text-lg font-semibold tracking-tight text-white">{{ config('betgo.name', 'BetGo') }}</span>
    </a>

    {{-- Primary action --}}
    @if ($nav['cta'])
        <div class="px-3 pt-4">
            <a href="{{ $nav['cta']['href'] }}" title="{{ $nav['cta']['label'] }}" @click="drawer = false"
                class="sb-center flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-3 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-700">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"
                    aria-hidden="true">{!! Nav::icon('plus') !!}</svg>
                <span class="sb-hide">{{ $nav['cta']['label'] }}</span>
            </a>
        </div>
    @endif

    {{-- Navigation --}}
    <nav class="flex-1 space-y-5 overflow-y-auto px-3 py-4" aria-label="Dashboard">
        @foreach ($nav['sections'] as $section)
            <div class="space-y-1">
                @if ($section['title'])
                    <p class="sb-hide px-3 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                        {{ $section['title'] }}</p>
                    <div class="sb-rule border-t border-slate-800"></div>
                @endif

                @foreach ($section['items'] as $item)
                    @if ($item['children'])
                        {{-- Group with sub-items --}}
                        <div x-data="{ open: {{ $item['active'] ? 'true' : 'false' }} }">
                            <button type="button" title="{{ $item['label'] }}" aria-label="{{ $item['label'] }}"
                                :aria-expanded="open.toString()"
                                aria-controls="grp-{{ \Illuminate\Support\Str::slug($item['label']) }}"
                                @click="if (collapsed && !mobile) { setCollapsed(false); open = true } else { open = !open }"
                                class="group sb-center relative flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors {{ $item['active'] ? 'bg-indigo-500/10 text-indigo-400' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" aria-hidden="true"
                                    class="shrink-0 {{ $item['active'] ? 'text-indigo-400' : 'text-slate-400 group-hover:text-white' }}">{!! Nav::icon($item['icon']) !!}</svg>
                                <span class="sb-hide flex-1 whitespace-nowrap text-left">{{ $item['label'] }}</span>

                                @if ($item['badge'])
                                    <span
                                        class="sb-hide rounded-full bg-amber-500 px-1.5 py-0.5 text-[10px] font-bold text-black">{{ $item['badge']['value'] }}</span>
                                    <span
                                        class="sb-dot absolute right-2 top-1.5 h-2.5 w-2.5 rounded-full border-2 border-slate-900 bg-amber-500"></span>
                                @endif

                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" aria-hidden="true"
                                    class="sb-hide shrink-0 transition-transform"
                                    :class="open ? 'rotate-90' : ''">{!! Nav::icon('chevron') !!}</svg>
                            </button>

                            <div id="grp-{{ \Illuminate\Support\Str::slug($item['label']) }}" x-show="open" x-cloak
                                x-transition.opacity class="sb-hide mt-1 space-y-0.5 pl-9 pr-1">
                                @foreach ($item['children'] as $child)
                                    <a href="{{ $child['href'] }}" @click="drawer = false"
                                        @if ($child['active']) aria-current="page" @endif
                                        class="flex items-center justify-between rounded-lg px-3 py-2 text-sm transition-colors {{ $child['active'] ? 'bg-indigo-500/5 font-medium text-indigo-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                        <span>{{ $child['label'] }}</span>
                                        @if ($child['badge'])
                                            <span
                                                class="rounded-full bg-amber-500 px-1.5 py-0.5 text-[10px] font-bold text-black">{{ $child['badge']['value'] }}</span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        {{-- Single link --}}
                        <a href="{{ $item['href'] }}" title="{{ $item['label'] }}" aria-label="{{ $item['label'] }}"
                            @click="drawer = false" @if ($item['active']) aria-current="page" @endif
                            class="group sb-center relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors {{ $item['active'] ? 'bg-indigo-500/10 text-indigo-400' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            @if ($item['active'])
                                <span class="absolute inset-y-2 left-0 w-0.5 rounded-full bg-indigo-400"></span>
                            @endif

                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
                                class="shrink-0 {{ $item['active'] ? 'text-indigo-400' : 'text-slate-400 group-hover:text-white' }}">{!! Nav::icon($item['icon']) !!}</svg>
                            <span class="sb-hide whitespace-nowrap">{{ $item['label'] }}</span>

                            @if ($b = $item['badge'])
                                @if ($b['type'] === 'live')
                                    <span x-cloak x-show="$store.badges.{{ $b['key'] }} > 0"
                                        x-text="$store.badges.{{ $b['key'] }} > 99 ? '99+' : $store.badges.{{ $b['key'] }}"
                                        class="sb-hide ml-auto rounded-full bg-indigo-500 px-2 py-0.5 text-xs font-bold text-white"></span>
                                    <span x-cloak x-show="$store.badges.{{ $b['key'] }} > 0"
                                        class="sb-dot absolute right-2 top-1.5 h-2.5 w-2.5 rounded-full border-2 border-slate-900 bg-indigo-500"></span>
                                @else
                                    <span
                                        class="sb-hide ml-auto rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ $b['tone'] === 'amber' ? 'bg-amber-500 text-black' : 'bg-indigo-500 text-white' }}">{{ $b['value'] }}</span>
                                    <span
                                        class="sb-dot absolute right-2 top-1.5 h-2.5 w-2.5 rounded-full border-2 border-slate-900 {{ $b['tone'] === 'amber' ? 'bg-amber-500' : 'bg-indigo-500' }}"></span>
                                @endif
                            @endif
                        </a>
                    @endif
                @endforeach
            </div>
        @endforeach
    </nav>

    {{-- Listing credits (owners and agents) --}}
    @if ($nav['credits'])
        <div class="sb-hide mx-3 mb-3 rounded-xl border border-slate-800 bg-slate-800/40 p-3.5">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">Listing credits</p>
            <p class="mt-1 text-lg font-bold {{ $nav['credits']['low'] ? 'text-amber-400' : 'text-white' }}">
                {{ $nav['credits']['label'] }}</p>
            @if ($nav['credits']['sub'])
                <p class="text-xs text-slate-500">{{ $nav['credits']['sub'] }}</p>
            @endif
            @if ($nav['credits']['href'])
                <a href="{{ $nav['credits']['href'] }}"
                    class="mt-2 inline-block text-xs font-semibold text-indigo-400 hover:text-indigo-300">Buy more →</a>
            @endif
        </div>
    @endif

    {{-- Footer links --}}
    <div class="space-y-1 border-t border-slate-800 p-3">
        @if ($nav['links']['profile'])
            <a href="{{ $nav['links']['profile'] }}" title="Settings" aria-label="Settings" @click="drawer = false"
                class="sb-center flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-300 transition-colors hover:bg-slate-800 hover:text-white">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 text-slate-400"
                    aria-hidden="true">{!! Nav::icon('settings') !!}</svg>
                <span class="sb-hide">Settings</span>
            </a>
        @endif
        <a href="{{ $nav['links']['home'] }}" target="_blank" rel="noopener" title="View website"
            aria-label="View website (opens in a new tab)"
            class="sb-center flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-300 transition-colors hover:bg-slate-800 hover:text-white">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 text-slate-400"
                aria-hidden="true">{!! Nav::icon('external-link') !!}</svg>
            <span class="sb-hide">View website</span>
        </a>
    </div>
</aside>
