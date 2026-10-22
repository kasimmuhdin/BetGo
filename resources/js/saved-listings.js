function notify(body) {
    if (window.showGlobalToast) {
        window.showGlobalToast({ title: 'Saved listings', body });
    } else {
        alert(body);
    }
}

async function send(url, method) {
    let res;

    try {
        res = await fetch(url, {
            method,
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        });
    } catch {
        throw new Error('Network error. Please check your connection and try again.');
    }

    if (res.status === 401 || res.status === 419) {
        throw new Error('Your session expired. Please refresh the page and log in again.');
    }

    if (!res.ok) {
        const data = await res.json().catch(() => ({}));
        throw new Error(data.message || 'Something went wrong. Please try again.');
    }
}

document.addEventListener('alpine:init', () => {
    // Heart button (cards + detail page)
    window.Alpine.data('saveButton', ({ url, saved = false }) => ({
        saved,
        busy: false,

        async toggle() {
            if (this.busy) return;

            const previous = this.saved;
            this.busy = true;
            this.saved = !previous; // optimistic: feels instant

            try {
                await send(url, previous ? 'DELETE' : 'POST');
            } catch (e) {
                this.saved = previous; // roll back
                notify(e.message);
            } finally {
                this.busy = false;
            }
        },
    }));

    // "Remove" button on the saved-listings page
    window.Alpine.data('unsaveButton', ({ url }) => ({
        removed: false,
        busy: false,

        async remove() {
            if (this.busy) return;
            this.busy = true;

            try {
                await send(url, 'DELETE');
                this.removed = true;
                this.$dispatch('listing-removed');
            } catch (e) {
                notify(e.message);
            } finally {
                this.busy = false;
            }
        },
    }));
});