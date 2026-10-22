@props(['name', 'label', 'value' => '', 'rows' => 14])

@php
    $id = $attributes->get('id', $name);
    $error = $errors->first($name);
@endphp

<div x-data="markdownField(@js(['url' => route('admin.markdown.preview')]))">
    <div class="mb-1.5 flex items-center justify-between gap-3">
        <label for="{{ $id }}" class="text-sm font-medium text-gray-200">{{ $label }}</label>

        <div role="tablist" aria-label="Editor mode"
            class="inline-flex rounded-lg border border-[#3a3a3d] bg-[#161414] p-0.5 text-xs font-semibold">
            <button type="button" role="tab" :aria-selected="(tab === 'write').toString()" @click="tab = 'write'"
                :class="tab === 'write' ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:text-white'"
                class="rounded-md px-3 py-1 transition">Write</button>
            <button type="button" role="tab" :aria-selected="(tab === 'preview').toString()" @click="preview()"
                :class="tab === 'preview' ? 'bg-indigo-600 text-white' : 'text-gray-400 hover:text-white'"
                class="rounded-md px-3 py-1 transition">Preview</button>
        </div>
    </div>

    <textarea x-ref="field" x-show="tab === 'write'" id="{{ $id }}" name="{{ $name }}"
        rows="{{ $rows }}"
        @if ($error) aria-invalid="true" aria-describedby="{{ $id }}-msg" @endif
        @class([
            'w-full rounded-lg border bg-[#161414] px-3 py-3 font-mono text-sm leading-relaxed text-gray-100 placeholder:text-gray-600 focus:outline-none focus:ring-1',
            'border-red-500 focus:border-red-500 focus:ring-red-500' => $error,
            'border-[#3a3a3d] focus:border-indigo-500 focus:ring-indigo-500' => !$error,
        ])>{{ old($name, $value) }}</textarea>

    <div x-show="tab === 'preview'" x-cloak class="min-h-[12rem] rounded-lg border border-[#3a3a3d] bg-[#161414] p-5">
        <p x-show="loading" class="text-sm text-gray-500">Loading preview…</p>
        <p x-show="failed" class="text-sm text-red-400">The preview could not be loaded. Please try again.</p>
        <p x-show="!loading && !failed && !html" class="text-sm text-gray-500">Nothing to preview yet.</p>
        <div x-show="!loading && !failed && html" class="md-body" x-html="html"></div>
    </div>

    @if ($error)
        <p id="{{ $id }}-msg" role="alert" class="mt-1.5 text-xs text-red-400">{{ $error }}</p>
    @endif

    <details class="mt-2 text-xs text-gray-500">
        <summary class="cursor-pointer select-none hover:text-gray-300">Formatting help</summary>
        <div
            class="mt-2 grid gap-x-6 gap-y-1 rounded-lg border border-[#3a3a3d] bg-[#161414] p-3 font-mono sm:grid-cols-2">
            <span>## Heading</span><span>makes a heading</span>
            <span>**bold**</span><span>makes bold text</span>
            <span>- item</span><span>makes a bullet list</span>
            <span>1. item</span><span>makes a numbered list</span>
            <span>[text](https://link)</span><span>makes a link</span>
            <span>[text](/pricing)</span><span>links to a page on this site</span>
            <span>Blank line</span><span>starts a new paragraph</span>
        </div>
    </details>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                window.Alpine.data('markdownField', ({
                    url
                }) => ({
                    tab: 'write',
                    html: '',
                    loading: false,
                    failed: false,

                    async preview() {
                        this.tab = 'preview';
                        this.loading = true;
                        this.failed = false;

                        try {
                            const res = await BG.fetch(url, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    content: this.$refs.field.value
                                }),
                            });
                            this.html = (await res.json()).html;
                        } catch (e) {
                            this.failed = true;
                        } finally {
                            this.loading = false;
                        }
                    },
                }));
            });
        </script>
    @endpush
@endonce
