@php $isNew = ! $faq->exists; @endphp

<form method="POST" action="{{ $action }}" x-data="{ dirty: false, submitting: false }" @input="dirty = true"
    @submit="submitting = true; dirty = false" @pageshow.window="submitting = false"
    @beforeunload.window="if (dirty) { $event.preventDefault(); $event.returnValue = '' }"
    class="space-y-5 rounded-xl border border-[#3a3a3d] bg-[#1e1b1b] p-6">
    @csrf
    @unless ($isNew)
        @method('PUT')
    @endunless

    <x-admin.input name="question" label="Question" :value="$faq->question" maxlength="255" required autofocus
        placeholder="For example: How do I list my property?" />

    <x-admin.markdown-field name="answer" label="Answer" :value="$faq->answer" rows="8" />

    <label class="flex cursor-pointer items-start gap-3">
        <input type="hidden" name="is_published" value="0">
        <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $faq->is_published ?? true))
            class="mt-0.5 h-4 w-4 rounded border-[#3a3a3d] bg-[#161414] text-indigo-600 focus:ring-indigo-500">
        <span>
            <span class="block text-sm font-medium text-gray-200">Show on the website</span>
            <span class="block text-xs text-gray-500">Turn off to keep it as a draft.</span>
        </span>
    </label>

    <div class="flex items-center justify-end gap-3 border-t border-[#3a3a3d] pt-5">
        <a href="{{ route('admin.faqs.index') }}"
            class="rounded-lg px-4 py-2 text-sm text-gray-400 transition hover:text-white">Cancel</a>
        <button type="submit" :disabled="submitting"
            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60">
            <svg x-show="submitting" x-cloak class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"
                aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
            <span
                x-text="submitting ? 'Saving…' : '{{ $isNew ? 'Add question' : 'Save changes' }}'">{{ $isNew ? 'Add question' : 'Save changes' }}</span>
        </button>
    </div>
</form>
