<?php

namespace App\Support;

use App\Models\Message;
use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Facades\Route;

class DashboardNav
{
    /**
     * Everything the sidebar and header need, computed once per request.
     * An item is listed only when its route exists, so the menu never has dead "#" links.
     */
    public static function build(User $user): array
    {
        $role = $user->role ?: 'user';
        $isAdmin = $role === 'admin';
        $isSeller = in_array($role, ['owner', 'agent'], true);
        $approved = in_array($user->status, [null, 'approved'], true);

        $url = static function (string $base) use ($role): ?string {
            foreach ([$role . '.' . $base, $base] as $name) {
                if (Route::has($name)) {
                    return route($name);
                }
            }

            return null;
        };

        $item = static fn(string $label, string $icon, ?string $href, array $match, ?array $badge = null): ?array => $href ? [
            'label' => $label,
            'icon' => $icon,
            'href' => $href,
            'active' => request()->routeIs(...$match),
            'badge' => $badge,
            'children' => [],
        ] : null;

        $counts = [
            'messages' => self::unreadMessages($user),
            'notifications' => $user->unreadNotifications()->count(),
            'pending_listings' => $isAdmin ? Property::where('status', 'pending')->count() : 0,
            'pending_users' => $isAdmin ? User::pendingVerification()->count() : 0,
        ];

        $sections = [[
            'title' => null,
            'items' => [
                $item('Dashboard', 'layout-dashboard', $url('dashboard'), [$role . '.dashboard', 'dashboard']),
                $item('Messages', 'message-square', $url('messages.index'), ['messages.*'], ['type' => 'live', 'key' => 'messages']),
            ],
        ]];

        if ($isAdmin) {
            $children = array_values(array_filter([
                $item('All listings', 'building-2', $url('listings.all'), ['admin.listings.all', 'admin.listings.show']),
                $item('Pending review', 'building-2', $url('listings.pending'), ['admin.listings.pending'], self::count($counts['pending_listings'], 'amber')),
            ]));
            $userchildren = array_values(array_filter([
                $item('All Users', 'users', $url('users.all'), ['admin.users.all', 'admin.users.show']),
                $item('Pending review', 'user-check', $url('users.pending'), ['admin.users.pending'], self::count($counts['pending_users'], 'amber')),
            ]));

            $listings = $children ? [
                'label' => 'Listings',
                'icon' => 'building-2',
                'href' => $children[0]['href'],
                'active' => (bool) array_filter($children, fn($c) => $c['active']),
                'badge' => self::count($counts['pending_listings'], 'amber'),
                'children' => $children,
            ] : null;
            $users = $userchildren ? [
                'label' => 'All Users',
                'icon' => 'users',
                'href' => $children[0]['href'],
                'active' => (bool) array_filter($userchildren, fn($c) => $c['active']),
                'badge' => self::count($counts['pending_users'], 'amber'),
                'children' => $userchildren,
            ] : null;

            $sections[] = ['title' => 'Manage', 'items' => [
                $listings,
                $users,
                $item('Payments', 'credit-card', $url('payments.index'), ['admin.payments.*']),
            ]];

            // These appear automatically once the admin pages exist.
            $sections[] = ['title' => 'Content', 'items' => [
                $item('Pages', 'file-text', $url('pages.index'), ['admin.pages.*', 'admin.settings.*']),
                $item('FAQs', 'help-circle', $url('faqs.index'), ['admin.faqs.*']),
                $item('Plans', 'tag', $url('plans.index'), ['admin.plans.*']),
            ]];
        }

        if ($isSeller) {
            $sections[] = ['title' => 'Manage', 'items' => [
                $item('My listings', 'building-2', $url('listings.index'), [$role . '.listings.*']),
                $item('Media library', 'image', $url('media.manage'), [$role . '.media.*', 'media.*']),
                $item('Billing & plans', 'credit-card', $url('payments.pricing'), ['payments.*']),
            ]];
        }

        if ($role === 'user') {
            $sections[] = ['title' => 'Explore', 'items' => [
                $item('Saved listings', 'heart', $url('saved-listings.index'), ['saved-listings.*']),
                $item('Browse listings', 'search', route('home') . '#listings', []),
            ]];
        }

        $sections = collect($sections)
            ->map(fn($s) => ['title' => $s['title'], 'items' => array_values(array_filter($s['items']))])
            ->filter(fn($s) => $s['items'])
            ->values()
            ->all();

        $createUrl = $isSeller && $approved ? $url('listings.create') : null;

        return [
            'sections' => $sections,
            'counts' => $counts,
            'cta' => $createUrl ? ['label' => 'New listing', 'href' => $createUrl] : null,
            'credits' => $isSeller ? self::credits($user) : null,
            'notice' => $isSeller ? self::notice($user) : null,
            'links' => [
                'messages' => $url('messages.index'),
                'profile' => Route::has('profile.edit') ? route('profile.edit') : null,
                'home' => route('home'),
            ],
        ];
    }

    private static function unreadMessages(User $user): int
    {
        return Message::where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->whereHas('conversation', function ($q) use ($user) {
                $q->where('initiator_id', $user->id)->orWhere('recipient_id', $user->id);
            })
            ->count();
    }

    private static function count(int $n, string $tone = 'indigo'): ?array
    {
        return $n > 0 ? ['type' => 'count', 'value' => $n, 'tone' => $tone] : null;
    }

    private static function credits(User $user): ?array
    {
        if (! method_exists($user, 'activeListingGrants')) {
            return null;
        }

        $grants = $user->activeListingGrants();

        if ($grants->contains(fn($g) => $g->plan->isUnlimited())) {
            return ['label' => 'Unlimited', 'sub' => null, 'low' => false, 'href' => null];
        }

        $left = (int) $grants->sum(fn($g) => $g->remaining());
        $next = $grants->filter(fn($g) => $g->expires_at)->sortBy('expires_at')->first();

        return [
            'label' => $left > 0 ? number_format($left) . ' left' : 'None left',
            'sub' => $next ? 'Next expiry ' . $next->expires_at->format('M d') : null,
            'low' => $left <= 3,
            'href' => Route::has('payments.pricing') ? route('payments.pricing') : null,
        ];
    }

    private static function notice(User $user): ?array
    {
        $who = $user->role === 'agent' ? 'agent' : 'owner';

        return match ($user->status) {
            'pending' => ['tone' => 'amber', 'text' => "Your {$who} request is being reviewed. You can post listings once an admin approves it."],
            'rejected' => ['tone' => 'red', 'text' => "Your {$who} request was rejected" . ($user->verification_rejection_reason ? ': ' . $user->verification_rejection_reason : '.') . ' Please contact support.'],
            default => null,
        };
    }

    public static function icon(string $name): string
    {
        return [
            'layout-dashboard' => '<rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/>',
            'building-2' => '<path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/>',
            'message-square' => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
            'credit-card' => '<rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/>',
            'heart' => '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>',
            'image' => '<rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>',
            'help-circle' => '<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/>',
            'user-check' => '<path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="m17 11 2 2 4-4"/>',
            'file-text' => '<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/>',
            'tag' => '<path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"/><circle cx="7.5" cy="7.5" r=".5" fill="currentColor"/>',
            'search' => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
            'plus' => '<path d="M5 12h14"/><path d="M12 5v14"/>',
            'chevron' => '<path d="m9 18 6-6-6-6"/>',
            'settings' => '<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>',
            'external-link' => '<path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>',
            'user' => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
            'users' => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
            'log-out' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>',
            'bell' => '<path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>',
            'menu' => '<path d="M4 6h16"/><path d="M4 12h16"/><path d="M4 18h16"/>',
            'panel-left' => '<rect width="18" height="18" x="3" y="3" rx="2"/><path d="M9 3v18"/>',
        ][$name] ?? '<circle cx="12" cy="12" r="9"/>';
    }
}
