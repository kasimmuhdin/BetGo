<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\ListingGrant;
use App\Models\Payment;
use App\Models\Plan;
use App\Services\ChapaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Cashier\Cashier;

class PaymentController extends Controller
{
    public function __construct(protected ChapaService $chapa) {}

    public function pricing(Request $request)
    {
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();
        $selected = $plans->firstWhere('key', $request->query('plan'));

        return view('payments.pricing', [
            'plans' => $plans,
            'activeSubscriptions' => auth()->user()->activeListingGrants(),
            'preselected' => $selected ? [
                'key' => $selected->key,
                'name' => $selected->name,
                'priceEtb' => number_format($selected->price, 0),
                'priceUsd' => $selected->price_usd ? number_format($selected->price_usd, 2) : '',
            ] : null,
        ]);
    }

    // ══════════ Chapa ══════════

    public function initiate(Request $request)
    {
        $validated = $request->validate(['plan_key' => ['required', 'exists:plans,key']]);
        $plan = Plan::where('key', $validated['plan_key'])->where('is_active', true)->firstOrFail();
        $user = $request->user();

        if (! $user->phone) {
            return redirect()->route('payments.pricing')
                ->with('error', 'Please add a phone number to your profile before paying.');
        }

        $txRef = 'BG-' . Str::upper(Str::random(10)) . '-' . time();

        $payment = Payment::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'provider' => 'chapa',
            'tx_ref' => $txRef,
            'amount' => $plan->price,
            'currency' => 'ETB',
            'status' => 'pending',
        ]);

        [$firstName, $lastName] = $this->splitName($user->name);

        $payload = [
            'amount' => (string) $plan->price,
            'currency' => 'ETB',
            'email' => $user->email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone_number' => $user->phone,
            'tx_ref' => $txRef,
            'callback_url' => route('payments.chapa.webhook'),
            'return_url' => route('payments.chapa.return', ['tx_ref' => $txRef]),
            'customization' => [
                'title' => 'BetGo Payment',
                'description' => "Payment for {$plan->name}",
            ],
        ];

        Log::info('Chapa initiate request', ['tx_ref' => $txRef, 'payload' => $payload]);
        try {
            $response = $this->chapa->initialize($payload);
        } catch (\Throwable $e) {
            Log::error('Chapa initialize failed to connect', ['tx_ref' => $txRef, 'message' => $e->getMessage()]);
            $payment->update(['status' => 'failed', 'meta' => ['error' => 'connection']]);

            return redirect()->route('payments.pricing')
                ->with('error', 'We could not reach Chapa. Please try again in a moment.');
        }
        Log::info('Chapa initiate response', ['tx_ref' => $txRef, 'response' => $response]);

        if (($response['status'] ?? null) !== 'success' || empty($response['data']['checkout_url'])) {
            $payment->update(['status' => 'failed', 'meta' => $response]);

            return redirect()->route('payments.pricing')
                ->with('error', 'Could not start the payment: ' . $this->extractErrorMessage($response));
        }

        $payment->update(['meta' => $response]);

        return redirect()->away($response['data']['checkout_url']);
    }

    public function webhook(Request $request)
    {
        $signature = $request->header('x-chapa-signature') ?? $request->header('Chapa-Signature');

        Log::info('Chapa webhook received', ['tx_ref' => $request->input('tx_ref')]);

        if (! $this->chapa->isValidWebhookSignature($request->getContent(), $signature)) {
            Log::warning('Chapa webhook signature invalid', ['tx_ref' => $request->input('tx_ref')]);
            return response()->json(['message' => 'invalid signature'], 401);
        }

        if ($txRef = $request->input('tx_ref')) {
            $this->finalizePayment($txRef);
        }

        return response()->json(['message' => 'ok']);
    }

    public function return(Request $request)
    {
        $txRef = $request->query('tx_ref');

        $payment = Payment::where('tx_ref', $txRef)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if ($payment->status === 'pending') {
            $this->finalizePayment($payment->tx_ref);
            $payment->refresh();
        }

        return view('payments.return', compact('payment'));
    }

    protected function finalizePayment(string $txRef): void
    {
        $payment = Payment::where('tx_ref', $txRef)->first();

        if (! $payment || $payment->status !== 'pending') {
            return;
        }

        $result = $this->chapa->verify($txRef);
        Log::info('Chapa verify result', ['tx_ref' => $txRef, 'result' => $result]);

        $isSuccess = ($result['status'] ?? null) === 'success'
            && ($result['data']['status'] ?? null) === 'success';

        if (! $isSuccess) {
            $payment->update(['status' => 'failed', 'meta' => $result]);
            return;
        }

        $paidAmount = (float) ($result['data']['amount'] ?? 0);
        if (round($paidAmount, 2) !== round((float) $payment->amount, 2)) {
            Log::warning('finalizePayment: amount mismatch', [
                'tx_ref' => $txRef,
                'paid' => $paidAmount,
                'expected' => $payment->amount,
            ]);
            $payment->update(['status' => 'failed', 'meta' => $result]);
            return;
        }

        $plan = $payment->plan;


        $grant = ListingGrant::create([
            'user_id' => $payment->user_id,
            'plan_id' => $plan->id,
            'listings_used' => 0,
            'start_at' => now(),
            'expire_at' => $plan->duration_days ? now()->addDays($plan->duration_days) : null,
            'status' => 'active',
        ]);

        $payment->update([
            'status' => 'success',
            'listing_grant_id' => $grant->id,
            'chapa_reference' => $result['data']['reference'] ?? null,
            'meta' => $result,
            'paid_at' => now(),
        ]);

        Log::info('finalizePayment: success, grant created', ['tx_ref' => $txRef, 'grant_id' => $grant->id]);
    }

    // ══════════ Stripe (via Cashier) ══════════

    public function initiateStripe(Request $request)
    {
        $validated = $request->validate(['plan_key' => ['required', 'exists:plans,key']]);
        $plan = Plan::where('key', $validated['plan_key'])->where('is_active', true)->firstOrFail();
        $user = $request->user();

        if (! $plan->stripe_price_id) {
            return redirect()->route('payments.pricing')
                ->with('error', 'Card payment is not set up for this plan yet. You can pay with Chapa instead.');
        }

        $options = [
            'success_url' => route('payments.stripe.return') . '?session_id={CHECKOUT_SESSION_ID}&plan_key=' . $plan->key,
            'cancel_url' => route('payments.pricing', ['cancelled' => 1]),
            'metadata' => ['plan_key' => $plan->key],
        ];

        try {
            $checkout = $plan->isRecurring()
                ? $user->newSubscription('default', $plan->stripe_price_id)->checkout($options)
                : $user->checkout([$plan->stripe_price_id => 1], $options);
        } catch (\Throwable $e) {
            Log::error('Stripe checkout could not be started', [
                'user_id' => $user->id,
                'plan' => $plan->key,
                'message' => $e->getMessage(),
            ]);

            return redirect()->route('payments.pricing')
                ->with('error', 'We could not open the card payment page. Please try again, or pay with Chapa.');
        }

        return redirect()->away($checkout->url);
    }

    public function stripeReturn(Request $request)
    {
        $sessionId = $request->query('session_id');
        abort_unless($sessionId, 404);

        $user = $request->user();

        try {
            $session = Cashier::stripe()->checkout->sessions->retrieve($sessionId);
        } catch (\Throwable $e) {
            Log::warning('Stripe return: could not retrieve session', [
                'session_id' => $sessionId,
                'message' => $e->getMessage(),
            ]);
            abort(404);
        }

        abort_unless($session->customer && $session->customer === $user->stripe_id, 403);

        $payment = Payment::where('provider', 'stripe')
            ->where('user_id', $user->id)
            ->whereIn('tx_ref', array_filter([$session->id, $session->invoice]))
            ->first();

        return view('payments.return', [
            'payment' => $payment,
            'processing' => $payment === null,
        ]);
    }
    protected function splitName(string $name): array
    {
        $parts = explode(' ', trim($name), 2);
        return [$parts[0], $parts[1] ?? $parts[0]];
    }


    protected function extractErrorMessage(array $response): string
    {
        $message = $response['message'] ?? 'unknown error';

        if (is_array($message)) {
            $first = collect($message)->first();
            return is_array($first) ? ($first[0] ?? 'unknown error') : (string) $first;
        }

        return (string) $message;
    }
}
