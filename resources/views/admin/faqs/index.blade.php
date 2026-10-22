@extends('layouts.app')
@section('title', 'FAQ')

@section('content')
    <div class="mx-auto max-w-4xl space-y-6" x-data="faqManager(@js(['items' => $items, 'reorderUrl' => route('admin.faqs.reorder')]))" @keydown.escape.window="confirm = null"
        @pageshow.window="deleting = false">

        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-white">FAQ</h1>
                <p class="mt-1 text-sm text-gray-400">Drag a row, or use the arrows, to change the order. Changes save
                    automatically.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('faq') }}" target="_blank" rel="noopener"
                    class="text-sm text-gray-400 transition hover:text-white">View live ↗</a>
                <a href="{{ route('admin.faqs.create') }}"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">+
                    Add question</a>
            </div>
        </div>

        <x-admin.flash />

        <div class="h-5 text-xs" aria-live="polite">
            <span x-show="saving" x-cloak class="text-gray-400">Saving order…</span>
            <span x-show="saved" x-cloak class="text-emerald-400">✓ Order saved</span>
        </div>

        <ul class="space-y-2" x-show="items.length" x-cloak>
            <template x-for="(item, i) in items" :key="item.id">
                <li draggable="true" @dragstart="start(i, $event)" @dragover.prevent="over(i)" @drop.prevent="drop(i)"
                    @dragend="end()"
                    :class="[dragIndex === i ? 'opacity-40' : '', overIndex === i && dragIndex !== i ? 'border-indigo-500' :
                        'border-[#3a3a3d]'
                    ]"
                    class="flex items-center gap-3 rounded-xl border bg-[#1e1b1b] p-3 transition-colors">

                    <span class="cursor-grab text-gray-600" title="Drag to reorder" aria-hidden="true">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <circle cx="9" cy="6" r="1.6" />
                            <circle cx="15" cy="6" r="1.6" />
                            <circle cx="9" cy="12" r="1.6" />
                            <circle cx="15" cy="12" r="1.6" />
                            <circle cx="9" cy="18" r="1.6" />
                            <circle cx="15" cy="18" r="1.6" />
                        </svg>
                    </span>

                    <span class="w-5 text-center text-xs text-gray-500" x-text="i + 1"></span>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium" :class="item.published ? 'text-gray-100' : 'text-gray-500'"
                            x-text="item.question"></p>
                        <p x-show="!item.published" class="text-[11px] text-amber-400">Hidden from the website</p>
                    </div>

                    <button type="button" role="switch" :aria-checked="item.published.toString()" @click="toggle(item)"
                        :aria-label="item.published ? 'Hide this question' : 'Publish this question'"
                        :class="item.published ? 'bg-emerald-500' : 'bg-gray-600'"
                        class="relative h-5 w-9 shrink-0 rounded-full transition-colors">
                        <span :class="item.published ? 'translate-x-4' : 'translate-x-0.5'"
                            class="absolute left-0 top-0.5 h-4 w-4 rounded-full bg-white transition-transform"></span>
                    </button>

                    <div class="flex items-center gap-1">
                        <button type="button" @click="move(i, -1)" :disabled="i === 0" aria-label="Move up"
                            class="rounded-md p-1.5 text-gray-400 transition hover:bg-[#2a2727] hover:text-white disabled:opacity-30 disabled:hover:bg-transparent">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                            </svg>
                        </button>
                        <button type="button" @click="move(i, 1)" :disabled="i === items.length - 1" aria-label="Move down"
                            class="rounded-md p-1.5 text-gray-400 transition hover:bg-[#2a2727] hover:text-white disabled:opacity-30 disabled:hover:bg-transparent">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <a :href="item.editUrl"
                            class="rounded-md px-2.5 py-1.5 text-xs font-semibold text-indigo-400 transition hover:bg-indigo-500/10">Edit</a>
                        <button type="button" @click="confirm = item"
                            class="rounded-md px-2.5 py-1.5 text-xs font-semibold text-red-400 transition hover:bg-red-500/10">Delete</button>
                    </div>
                </li>
            </template>
        </ul>

        <div x-show="!items.length" x-cloak
            class="rounded-xl border border-dashed border-[#3a3a3d] bg-[#1e1b1b] px-6 py-14 text-center">
            <p class="font-medium text-gray-300">No questions yet</p>
            <p class="mt-1 text-sm text-gray-500">Add the questions your visitors ask most often.</p>
            <a href="{{ route('admin.faqs.create') }}"
                class="mt-5 inline-flex rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">+
                Add question</a>
        </div>

        {{-- Delete confirmation --}}
        <div x-show="confirm" x-cloak x-transition.opacity class="fixed inset-0 z-[90] flex items-center justify-center p-4"
            role="dialog" aria-modal="true" aria-labelledby="delete-title">
            <div class="absolute inset-0 bg-black/70" @click="confirm = null"></div>

            <form method="POST" :action="confirm ? confirm.deleteUrl : '#'" @submit="deleting = true"
                class="relative w-full max-w-md rounded-xl border border-[#3a3a3d] bg-[#1e1b1b] p-6 shadow-2xl">
                @csrf
                @method('DELETE')

                <h3 id="delete-title" class="text-lg font-semibold text-white">Delete this question?</h3>
                <p class="mt-2 rounded-lg bg-[#161414] px-3 py-2 text-sm text-gray-300"
                    x-text="confirm ? confirm.question : ''"></p>
                <p class="mt-3 text-sm text-gray-500">This cannot be undone. To keep it but remove it from the website,
                    switch it off instead.</p>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="confirm = null" :disabled="deleting"
                        class="rounded-lg border border-[#3a3a3d] px-4 py-2 text-sm text-gray-300 transition hover:bg-[#2a2727]">Cancel</button>
                    <button type="submit" :disabled="deleting"
                        class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700 disabled:opacity-60">
                        <svg x-show="deleting" x-cloak class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <span x-text="deleting ? 'Deleting…' : 'Delete'">Delete</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            window.Alpine.data('faqManager', ({
                items,
                reorderUrl
            }) => ({
                items,
                reorderUrl,
                dragIndex: null,
                overIndex: null,
                saving: false,
                saved: false,
                confirm: null,
                deleting: false,

                start(i, event) {
                    this.dragIndex = i;
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', String(
                    i)); // Firefox needs data to start a drag
                },
                over(i) {
                    if (this.dragIndex !== null) this.overIndex = i;
                },
                end() {
                    this.dragIndex = null;
                    this.overIndex = null;
                },

                async drop(i) {
                    const from = this.dragIndex;
                    this.end();
                    if (from === null || from === i) return;

                    const previous = [...this.items];
                    const [moved] = this.items.splice(from, 1);
                    this.items.splice(i, 0, moved);
                    await this.save(previous);
                },

                async move(i, step) {
                    const j = i + step;
                    if (j < 0 || j >= this.items.length) return;

                    const previous = [...this.items];
                    [this.items[i], this.items[j]] = [this.items[j], this.items[i]];
                    await this.save(previous);
                },

                async save(previous) {
                    this.saving = true;
                    this.saved = false;

                    try {
                        await BG.fetch(this.reorderUrl, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                ids: this.items.map((f) => f.id)
                            }),
                        });
                        this.saved = true;
                        setTimeout(() => (this.saved = false), 2500);
                    } catch (e) {
                        this.items = previous; // put the list back the way it was
                        BG.toast({
                            title: 'Could not save the order',
                            body: 'Reload the page and try again.'
                        });
                    } finally {
                        this.saving = false;
                    }
                },

                async toggle(item) {
                    const previous = item.published;
                    item.published = !previous; // optimistic

                    try {
                        const res = await BG.fetch(item.toggleUrl, {
                            method: 'PATCH'
                        });
                        item.published = (await res.json()).published;
                    } catch (e) {
                        item.published = previous;
                        BG.toast({
                            title: 'Could not update the question',
                            body: 'Please try again.'
                        });
                    }
                },
            }));
        });
    </script>
@endpush
