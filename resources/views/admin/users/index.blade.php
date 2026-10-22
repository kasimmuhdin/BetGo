@extends('layouts.app')
@section('title', 'All Users')
@section('content')
    <div class="max-w-7xl mx-auto px-4 py-6 text-white space-y-6">

        <div>
            <h1 class="text-2xl font-bold tracking-tight">All Users</h1>
            <p class="text-sm text-gray-400 mt-1">Pending, approval and reject stat is only include owners and agents</p>
        </div>

        @if (session('success'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition.opacity.duration.500ms
                class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-xl text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-gradient-to-br from-[#232020] to-[#1e1b1b] border border-[#3a3a3d] rounded-xl p-5">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">All Users</p>
                <p class="text-3xl font-extrabold text-blue-400 mt-2">{{ $stats['all'] }}</p>
            </div>
            <div class="bg-gradient-to-br from-[#232020] to-[#1e1b1b] border border-[#3a3a3d] rounded-xl p-5">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Pending</p>
                <p class="text-3xl font-extrabold text-amber-400 mt-2">{{ $stats['pending_count'] }}</p>
            </div>
            <div class="bg-gradient-to-br from-[#232020] to-[#1e1b1b] border border-[#3a3a3d] rounded-xl p-5">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Owners Waiting</p>
                <p class="text-3xl font-extrabold text-indigo-400 mt-2">{{ $stats['owners_pending'] }}</p>
            </div>
            <div class="bg-gradient-to-br from-[#232020] to-[#1e1b1b] border border-[#3a3a3d] rounded-xl p-5">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Agents Waiting</p>
                <p class="text-3xl font-extrabold text-cyan-400 mt-2">{{ $stats['agents_pending'] }}</p>
            </div>
            <div class="bg-gradient-to-br from-[#232020] to-[#1e1b1b] border border-[#3a3a3d] rounded-xl p-5">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Approved </p>
                <p class="text-3xl font-extrabold text-emerald-400 mt-2">{{ $stats['approved'] }}</p>
            </div>
            <div class="bg-gradient-to-br from-[#232020] to-[#1e1b1b] border border-[#3a3a3d] rounded-xl p-5">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Rejected </p>
                <p class="text-3xl font-extrabold text-red-400 mt-2">{{ $stats['rejected'] }}</p>
            </div>
        </div>

        {{-- Filters --}}
        <form method="GET" class="bg-[#1e1b1b] border border-[#3a3a3d] rounded-xl p-4 flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-500" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"></path>
                </svg>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by name or email…"
                    class="w-full pl-9 pr-3 py-2.5 rounded-lg bg-[#161414] border border-[#3a3a3d] text-white text-sm placeholder:text-gray-500 focus:outline-none focus:border-indigo-500">
            </div>

            <select name="role"
                class="rounded-lg px-3 py-2.5 bg-[#161414] border border-[#3a3a3d] text-sm text-white focus:outline-none focus:border-indigo-500">
                <option value="" class="bg-[#1e1b1b]">All Users</option>
                <option value="owner" class="bg-[#1e1b1b]" @selected(request('role') === 'owner')>Owners</option>
                <option value="agent" class="bg-[#1e1b1b]" @selected(request('role') === 'agent')>Agents</option>
                <option value="agent" class="bg-[#1e1b1b]" @selected(request('role') === 'user')>Users</option>
            </select>

            <button type="submit"
                class="px-5 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-sm font-medium transition">Filter</button>

            @if (request()->hasAny(['q', 'role']))
                <a href="{{ route('admin.users.all') }}"
                    class="px-4 py-2.5 rounded-lg border border-[#3a3a3d] text-sm text-gray-300 hover:bg-[#2a2727] transition text-center">Clear</a>
            @endif
        </form>

        {{-- Table --}}
        <div class="bg-[#1e1b1b] border border-[#3a3a3d] rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-[#161414] text-xs uppercase font-semibold text-gray-400">
                        <tr>
                            <th class="px-6 py-4">Applicant</th>
                            <th class="px-6 py-4">Requested Role</th>
                            <th class="px-6 py-4">National ID</th>
                            <th class="px-6 py-4">TIN</th>
                            <th class="px-6 py-4">Requested</th>
                            <th class="px-6 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#3a3a3d]">
                        @forelse ($requests as $user)
                            <tr class="hover:bg-[#2a2727] transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-9 h-9 rounded-full bg-indigo-500/20 flex items-center justify-center text-indigo-400 text-sm font-bold shrink-0">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-medium text-gray-200 truncate max-w-[200px]">{{ $user->name }}
                                            </p>
                                            <p class="text-xs text-gray-500 truncate max-w-[200px]">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="px-2.5 py-1 rounded-md text-xs font-semibold border {{ $user->role === 'owner' ? 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20' : 'bg-cyan-500/10 text-cyan-400 border-cyan-500/20' }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-300 font-mono text-xs">{{ $user->national_id ?? '—' }}</td>
                                <td class="px-6 py-4 text-gray-300 font-mono text-xs">{{ $user->tin ?? '—' }}</td>
                                <td class="px-6 py-4 text-gray-500 text-xs whitespace-nowrap">
                                    {{ $user->updated_at->diffForHumans() }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.users.show', $user) }}"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-indigo-600/10 border border-indigo-600/30 text-indigo-400 hover:bg-indigo-600 hover:text-white text-xs font-semibold transition">
                                        Review
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <svg class="w-12 h-12 text-[#3a3a3d] mx-auto mb-3" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <p class="text-gray-400 font-medium">All caught up</p>
                                    <p class="text-sm text-gray-600 mt-1">No verification requests waiting.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-2">{{ $requests->links('vendor.pagination.custom-dark') }}</div>
    </div>
@endsection
