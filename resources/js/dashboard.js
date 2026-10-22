const body = document.body;
const BG = (window.BG = window.BG || {});

/** Only same-site links are allowed (blocks javascript: URLs). */
BG.safeUrl = (url, fallback = '#') => {
    try {
        const u = new URL(url, window.location.origin);
        return u.origin === window.location.origin ? u.pathname + u.search + u.hash : fallback;
    } catch {
        return fallback;
    }
};

BG.csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

BG.fetch = async (url, options = {}) => {
    const res = await fetch(url, {
        credentials: 'same-origin',
        ...options,
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': BG.csrf(),
            ...(options.headers || {}),
        },
    });

    if (res.status === 401 || res.status === 419) {
        BG.toast({ title: 'Session expired', body: 'Please refresh the page and log in again.' });
        throw new Error('session-expired');
    }
    if (!res.ok) throw new Error('http-' + res.status);

    return res;
};

/** Toasts are built from DOM nodes with textContent, so message text can never run as HTML. */
const MAX_TOASTS = 4;

BG.toast = ({ avatarLetter = null, title = '', body: text = '', url = null } = {}) => {
    const box = document.getElementById('toast-container');
    if (!box) return;

    while (box.children.length >= MAX_TOASTS) box.firstElementChild.remove();

    const el = document.createElement(url ? 'a' : 'div');
    if (url) el.href = BG.safeUrl(url);
    el.className =
        'block w-72 translate-x-full rounded-lg border border-indigo-500 bg-[#1e1b1b] p-4 text-white shadow-xl transition-all duration-300 hover:bg-[#2a2727]';

    const row = document.createElement('div');
    row.className = 'flex items-start gap-3';

    if (avatarLetter) {
        const avatar = document.createElement('div');
        avatar.className = 'flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-600 font-bold';
        avatar.textContent = String(avatarLetter).slice(0, 1).toUpperCase();
        row.appendChild(avatar);
    }

    const col = document.createElement('div');
    col.className = 'min-w-0 flex-1';
    const h = document.createElement('p');
    h.className = 'truncate text-sm font-semibold';
    h.textContent = title;
    const p = document.createElement('p');
    p.className = 'mt-1 line-clamp-2 break-words text-xs text-gray-400';
    p.textContent = text || '';
    col.append(h, p);
    row.appendChild(col);
    el.appendChild(row);
    box.appendChild(el);

    let timer;
    const close = () => {
        el.classList.add('translate-x-full');
        setTimeout(() => el.remove(), 300);
    };
    const start = () => (timer = setTimeout(close, 5000));
    el.addEventListener('mouseenter', () => clearTimeout(timer));
    el.addEventListener('mouseleave', start);

    requestAnimationFrame(() => requestAnimationFrame(() => el.classList.remove('translate-x-full')));
    start();
};

window.showGlobalToast = BG.toast; // used by saved-listings.js

document.addEventListener('alpine:init', () => {
    const Alpine = window.Alpine;

    // One source of truth for the counters (sidebar, header and Echo all use it).
    Alpine.store('badges', {
        messages: Number(body.dataset.messages) || 0,
        notifications: Number(body.dataset.notifications) || 0,
    });

    // Sidebar and drawer state. The collapsed state is a class on <html>, set before first paint.
    Alpine.data('dashboardShell', () => ({
        drawer: false,
        mobile: window.matchMedia('(max-width: 767.98px)').matches,
        collapsed: document.documentElement.classList.contains('sb-collapsed'),

        init() {
            const mq = window.matchMedia('(max-width: 767.98px)');
            mq.addEventListener('change', (e) => {
                this.mobile = e.matches;
                if (!e.matches) this.drawer = false;
            });
        },

        toggleSidebar() {
            if (this.mobile) this.drawer = !this.drawer;
            else this.setCollapsed(!this.collapsed);
        },

        setCollapsed(value) {
            this.collapsed = value;
            document.documentElement.classList.toggle('sb-collapsed', value);
            try {
                localStorage.setItem('bg.sidebar', value ? 'collapsed' : 'open');
            } catch {}
        },
    }));

    Alpine.data('notificationBell', ({ indexUrl, readUrl, readAllUrl }) => ({
        open: false,
        items: [],
        loading: false,
        loaded: false,
        error: false,

        toggle() {
            this.open = !this.open;
            if (this.open && !this.loaded) this.load();
        },

        async load() {
            this.loading = true;
            this.error = false;
            try {
                this.items = await (await BG.fetch(indexUrl)).json();
                this.$store.badges.notifications = this.items.length;
                this.loaded = true;
            } catch {
                this.error = true;
            } finally {
                this.loading = false;
            }
        },

        async read(id) {
            // keepalive lets the request finish even while the browser leaves this page.
            this.items = this.items.filter((n) => n.id !== id);
            this.$store.badges.notifications = Math.max(0, this.$store.badges.notifications - 1);
            try {
                await BG.fetch(readUrl.replace('__ID__', id), { method: 'POST', keepalive: true });
            } catch {}
        },

        async readAll() {
            const previous = [...this.items];
            this.items = [];
            this.$store.badges.notifications = 0;
            try {
                await BG.fetch(readAllUrl, { method: 'POST' });
            } catch {
                this.items = previous;
                this.$store.badges.notifications = previous.length;
            }
        },

        timeAgo(iso) {
            const s = Math.floor((Date.now() - new Date(iso)) / 1000);
            if (s < 60) return 'just now';
            if (s < 3600) return Math.floor(s / 60) + 'm ago';
            if (s < 86400) return Math.floor(s / 3600) + 'h ago';
            return Math.floor(s / 86400) + 'd ago';
        },
    }));
});

/* ---------------- Real-time (optional: the page works without it) ---------------- */
const userId = body.dataset.userId;
const NOTIFICATION_EVENTS = [
    '.listing.submitted',
    '.listing.reviewed',
    '.user.verification.requested',
    '.user.verification.reviewed',
];

function connectEcho(attempt = 0) {
    if (!userId) return;

    if (!window.Echo) {
        if (attempt < 40) setTimeout(() => connectEcho(attempt + 1), 150); // give up after ~6 seconds
        return;
    }

    try {
        window.onlineUsers = [];

        window.Echo.join('app')
            .here((users) => {
                window.onlineUsers = users;
                window.dispatchEvent(new Event('presence-changed'));
            })
            .joining((user) => {
                if (!window.onlineUsers.some((u) => u.id === user.id)) window.onlineUsers.push(user);
                window.dispatchEvent(new Event('presence-changed'));
            })
            .leaving((user) => {
                window.onlineUsers = window.onlineUsers.filter((u) => u.id !== user.id);
                window.dispatchEvent(new Event('presence-changed'));
            });

        const channel = window.Echo.private(`App.Models.User.${userId}`);

        channel.listen('.message.sent', (e) => {
            if (window.location.pathname === `/messages/${e.conversation_id}`) return;

            BG.toast({
                avatarLetter: (e.sender_name || '?')[0],
                title: `${e.sender_name} sent a message`,
                body: e.body || 'Sent an image',
                url: `/messages/${e.conversation_id}`,
            });
            window.Alpine.store('badges').messages++;
            window.dispatchEvent(new CustomEvent('new-message'));
        });

        NOTIFICATION_EVENTS.forEach((name) =>
            channel.listen(name, (e) => {
                BG.toast({ title: e.title || 'New notification', body: e.message, url: e.url });
                window.Alpine.store('badges').notifications++;
                window.dispatchEvent(new CustomEvent('new-notification'));
            }),
        );
    } catch (error) {
        console.warn('Real-time updates are unavailable.', error);
    }
}

document.addEventListener('DOMContentLoaded', () => connectEcho());