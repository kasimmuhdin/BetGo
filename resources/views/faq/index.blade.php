@extends('layouts.guest')

@section('title', 'FAQ')
@section('meta_description', 'Answers to common questions about searching, listing, verification and payments on
    BetGo.')
@section('full_width', '1')

@push('styles')
    @vite('resources/css/home.css')
@endpush

@section('content')
    <section class="hp-page-hero" aria-labelledby="faq-title">
        <div class="hp-wrap">
            <p class="hp-kicker">FAQ</p>
            <h1 id="faq-title" class="hp-h1">Frequently asked questions</h1>
            <p class="hp-lead">Quick answers about searching, listing, verification and payments.</p>
        </div>
    </section>

    <section class="hp-section" style="padding-top:32px" x-data="{ q: '', texts: @js($items->pluck('search')->values()), show(i) { const v = this.q.trim().toLowerCase(); return v === '' || this.texts[i].includes(v); } }">
        <div class="hp-wrap" style="max-width:780px">

            @if ($items->count() > 6)
                <label class="hp-faq-search">
                    <span class="sr-only">Search the questions</span>
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <input type="search" x-model="q" placeholder="Search the questions" autocomplete="off">
                </label>
            @endif

            @if ($items->isEmpty())
                <p class="hp-note">Questions will be added soon.</p>
            @else
                <div class="hp-faq" style="margin-top:0">
                    @foreach ($items as $item)
                        <details x-show="show({{ $loop->index }})">
                            <summary>{{ $item['question'] }}</summary>
                            <div class="md-body">{{ $item['html'] }}</div>
                        </details>
                    @endforeach
                </div>

                <p x-cloak x-show="q.trim() !== '' && !texts.some((t, i) => show(i))" class="hp-note">No question matches
                    your search.</p>
            @endif

            @if (Route::has('pages.show'))
                <div class="hp-faq-help">
                    <h2>Still have a question?</h2>
                    <p>Our team is happy to help.</p>
                    <a href="{{ route('pages.show', 'contact') }}" class="hp-btn hp-btn--primary hp-btn--sm">Contact us</a>
                </div>
            @endif
        </div>
    </section>

    @if ($items->isNotEmpty())
        <script type="application/ld+json">
            {!! json_encode(
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'FAQPage',
                    'mainEntity' => $items->map(fn ($i) => [
                        '@type' => 'Question',
                        'name' => $i['question'],
                        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $i['plain']],
                    ])->values(),
                ],
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG,
            ) !!}
        </script>
    @endif
@endsection
