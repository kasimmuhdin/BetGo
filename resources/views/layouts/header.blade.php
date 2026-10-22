@php
    use App\Support\DashboardNav as Nav;

    $links = $nav['links'];
    $avatar = $user->avatar ?? null;
@endphp

<header
    class="flex h-[60px] shrink-0 items-center justify-between gap-3 border-b border-gray-700 bg-gray-800 px-3 sm:px-4">

    {{-- Sidebar toggle: drawer on phones, collapse on desktop --}}
    <button type="button" @click="toggleSidebar()" aria-controls="sidebar"
        :aria-expanded="(mobile ? drawer : !collapsed).toString()"
        :aria-label="mobile ? 'Open menu' : (collapsed ? 'Expand sidebar' : 'Collapse sidebar')"
        class="rounded-lg p-2 text-gray-200 transition hover:bg-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
        <svg class="md:hidden" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            aria-hidden="true">{!! Nav::icon('menu') !!}</svg>
        <svg class="hidden md:block" width="22" height="22" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            aria-hidden="true">{!! Nav::icon('panel-left') !!}</svg>
    </button>

    <div class="flex items-center gap-1 sm:gap-2">

        {{-- Messages --}}
        @if ($links['messages'])
            <a href="{{ $links['messages'] }}" aria-label="Messages"
                class="relative rounded-lg p-2 text-gray-200 transition hover:bg-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    aria-hidden="true">{!! Nav::icon('message-square') !!}</svg>
                <span x-cloak x-show="$store.badges.messages > 0"
                    x-text="$store.badges.messages > 9 ? '9+' : $store.badges.messages"
                    class="absolute -right-0.5 -top-0.5 flex h-[18px] min-w-[18px] items-center justify-center rounded-full border-2 border-gray-800 bg-indigo-500 px-1 text-[10px] font-bold text-white"></span>
            </a>
        @endif

        {{-- Notifications --}}
        <div class="relative" x-data="notificationBell(@js([
    'indexUrl' => route('notifications.index'),
    'readUrl' => route('notifications.read', '__ID__'),
    'readAllUrl' => route('notifications.read-all'),
]))" @new-notification.window="load()" @click.outside="open = false"
            @keydown.escape="open = false">

            <button type="button" @click="toggle()" aria-haspopup="true" :aria-expanded="open.toString()"
                :aria-label="$store.badges.notifications > 0 ? 'Notifications, ' + $store.badges.notifications + ' unread' :
                    'Notifications'"
                class="relative rounded-lg p-2 text-gray-200 transition hover:bg-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    aria-hidden="true">{!! Nav::icon('bell') !!}</svg>
                <span x-cloak x-show="$store.badges.notifications > 0"
                    x-text="$store.badges.notifications > 9 ? '9+' : $store.badges.notifications"
                    class="absolute -right-0.5 -top-0.5 flex h-[18px] min-w-[18px] items-center justify-center rounded-full border-2 border-gray-800 bg-red-500 px-1 text-[10px] font-bold text-white"></span>
            </button>

            <div x-show="open" x-cloak x-transition.opacity
                class="absolute right-0 top-full z-50 mt-2 max-h-96 w-80 max-w-[calc(100vw-1.5rem)] overflow-y-auto rounded-lg border border-[#3a3a3d] bg-[#1e1b1b] shadow-xl">

                <div
                    class="sticky top-0 flex items-center justify-between border-b border-[#3a3a3d] bg-[#1e1b1b] px-4 py-2.5">
                    <span class="text-sm font-semibold text-white">Notifications</span>
                    <button type="button" x-show="items.length" @click="readAll()"
                        class="text-xs font-medium text-indigo-400 transition hover:text-indigo-300">Mark all
                        read</button>
                </div>

                <div x-show="loading" class="flex flex-col items-center gap-2 px-4 py-8">
                    <svg class="h-5 w-5 animate-spin text-indigo-400" fill="none" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    <span class="text-xs text-gray-500">Loading…</span>
                </div>

                <div x-show="error && !loading" class="px-4 py-8 text-center">
                    <p class="text-sm text-gray-400">We could not load your notifications.</p>
                    <button type="button" @click="load()"
                        class="mt-2 text-sm font-medium text-indigo-400 hover:text-indigo-300">Try again</button>
                </div>

                <div x-show="loaded && !loading && !error && !items.length" class="px-4 py-8 text-center">
                    <p class="text-sm text-gray-500">You're all caught up.</p>
                </div>

                <template x-for="n in items" :key="n.id">
                    <a :href="BG.safeUrl(n.data.url)" @click="read(n.id)"
                        class="block border-b border-[#3a3a3d] bg-indigo-500/5 px-4 py-3 transition last:border-0 hover:bg-[#2a2727]">
                        <div class="flex items-start gap-2">
                            <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-indigo-400"></span>
                            <div class="min-w-0">
                                <p class="break-words text-sm text-gray-200" x-text="n.data.message"></p>
                                <p class="mt-1 text-[11px] text-gray-500" x-text="timeAgo(n.created_at)"></p>
                            </div>
                        </div>
                    </a>
                </template>
            </div>
        </div>

        {{-- Account menu --}}
        <div class="relative" x-data="{ menu: false, signingOut: false }" @click.outside="menu = false" @keydown.escape="menu = false">
            <button type="button" @click="menu = !menu" aria-haspopup="true" :aria-expanded="menu.toString()"
                aria-label="Account menu"
                class="flex items-center gap-2 rounded-lg p-1 transition hover:bg-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
                @if ($avatar)
                    <img src="{{ $avatar }}" alt="" width="32" height="32"
                        referrerpolicy="no-referrer" class="h-8 w-8 rounded-full object-cover">
                @else
                    <span
                        class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600 text-sm font-bold text-white">{{ strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                @endif
                <svg class="hidden h-4 w-4 text-gray-400 transition-transform sm:block"
                    :class="menu ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <div x-show="menu" x-cloak x-transition.opacity
                class="absolute right-0 top-full z-50 mt-2 w-60 rounded-lg border border-[#3a3a3d] bg-[#1e1b1b] py-1 shadow-xl">
                <div class="border-b border-[#3a3a3d] px-4 py-3">
                    <p class="truncate text-sm font-medium text-white">{{ $user->name }}</p>
                    <p class="truncate text-xs text-gray-500">{{ $user->email }}</p>
                    <span
                        class="mt-2 inline-block rounded-full bg-indigo-500/15 px-2 py-0.5 text-[11px] font-semibold capitalize text-indigo-300">{{ $user->role }}</span>
                </div>

                @if ($links['profile'])
                    <a href="{{ $links['profile'] }}"
                        class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-300 transition-colors hover:bg-[#2a2727] hover:text-white">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            aria-hidden="true">{!! Nav::icon('user') !!}</svg>
                        My profile
                    </a>
                @endif
                <a href="{{ $links['home'] }}" target="_blank" rel="noopener"
                    class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-300 transition-colors hover:bg-[#2a2727] hover:text-white">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        aria-hidden="true">{!! Nav::icon('external-link') !!}</svg>
                    View website
                </a>

                <form method="POST" action="{{ route('logout') }}" @submit="signingOut = true"
                    class="border-t border-[#3a3a3d]">
                    @csrf
                    <button type="submit" :disabled="signingOut" :aria-busy="signingOut.toString()"
                        class="flex w-full items-center gap-2.5 px-4 py-2 text-left text-sm text-red-400 transition-colors hover:bg-[#2a2727] hover:text-red-300 disabled:opacity-70">
                        <svg x-show="!signingOut" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            aria-hidden="true">{!! Nav::icon('log-out') !!}</svg>
                        <svg x-show="signingOut" x-cloak class="h-4 w-4 animate-spin" fill="none"
                            viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                            </path>
                        </svg>
                        <span x-text="signingOut ? 'Logging out…' : 'Log out'">Log out</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
