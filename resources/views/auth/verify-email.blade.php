@extends('layouts.auth')
@section('title', 'Verify your email')
@section('card_class', 'au-card--center')

@section('content')
    @php $status = session('status'); @endphp

    <div class="au-icon-lg"><i class="fa-regular fa-envelope" aria-hidden="true"></i></div>
    <h1 class="au-title">Check your email</h1>
    <p class="au-sub">We sent a verification link to <strong>{{ auth()->user()->email }}</strong>. Open it to finish setting up your account.</p>

    @if ($status === 'verification-link-sent')
        <div class="au-alert au-alert--ok" role="status" style="text-align:left">
            <i class="fa-solid fa-circle-check" aria-hidden="true"></i> A new link is on its way. It can take a minute to arrive.
        </div>
    @elseif ($status === 'verification-link-failed')
        <div class="au-alert au-alert--err" role="alert" style="text-align:left">
            <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> We could not send the email just now. Please press Resend in a minute.
        </div>
    @elseif ($status === 'verification-link-expired')
        <div class="au-alert au-alert--err" role="alert" style="text-align:left">
            <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> That link has expired. Request a new one below.
        </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}" data-loading>
        @csrf
        <button type="submit" class="au-btn au-btn--primary" id="resendBtn" data-loading-text="Sending…" data-text="Resend email"
            data-cooldown="{{ in_array($status, ['verification-link-sent', 'verification-link-first'], true) ? 60 : 0 }}">
            <span class="au-spin" aria-hidden="true"></span><span class="au-btn-text">Resend email</span>
        </button>
    </form>

    <p class="au-hint" style="margin-top:16px">Not there? Check your spam folder, or make sure the address above is right.</p>

    <form method="POST" action="{{ route('logout') }}" data-loading>
        @csrf
        <button type="submit" class="au-btn au-btn--link" data-loading-text="Logging out…" data-text="Log out">
            <span class="au-spin" aria-hidden="true"></span><span class="au-btn-text">Log out</span>
        </button>
    </form>
@endsection

@push('scripts')
    <script>
        (function () {
            var btn = document.getElementById('resendBtn');
            var left = parseInt(btn.dataset.cooldown, 10) || 0;
            if (!left) return;
            var label = btn.querySelector('.au-btn-text');
            btn.disabled = true;
            label.textContent = 'Resend in ' + left + 's';
            var t = setInterval(function () {
                left--;
                if (left <= 0) { clearInterval(t); btn.disabled = false; label.textContent = 'Resend email'; return; }
                label.textContent = 'Resend in ' + left + 's';
            }, 1000);
        })();
    </script>
@endpush