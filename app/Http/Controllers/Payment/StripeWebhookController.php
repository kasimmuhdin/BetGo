<?php

namespace App\Http\Controllers\Payment;

use App\Models\ListingGrant;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Http\Controllers\WebhookController;

class StripeWebhookController extends WebhookController
{

    public function handleCheckoutSessionCompleted(array $payload)
    {
        $session = $payload['data']['object'] ?? null;
        $planKey = $session['metadata']['plan_key'] ?? null;
        $user = $this->getUserByStripeId($session['customer']);
        Log::info('checkout.session.completed received', [
            'session_id' => $session['id'] ?? null,
            'customer' => $session['customer'] ?? null,
            'plan_key' => $planKey,
            'payment_status' => $session['payment_status'] ?? null,
            'has_subscription' => ! empty($session['subscription']),
            'user_found' => (bool) $user,
        ]);
        if (!$user || !$planKey) {
            Log::warning('checkout.session.completed: stopped, missing user or plan_key');

            return $this->successMethod();
        }
        $plan = Plan::where('key', $planKey)->first();

        if (! $plan) {
            Log::warning('checkout.session.completed: stopped, plan not found', ['plan_key' => $planKey]);
            return $this->successMethod();
        }

        if (! empty($session['subscription']) || ($session['payment_status'] ?? null) !== 'paid') {
            Log::info('checkout.session.completed: skipped (subscription or not paid yet)');
            return $this->successMethod();
        }

        $this->recordPayment(
            $user,
            $plan,
            $session['id'],
            ($session['amount_total'] ?? 0) / 100,
            strtoupper($session['currency'] ?? 'usd'),
            $session
        );

        return $this->successMethod();
    }

    public function handleCustomerSubscriptionCreated(array $payload)
    {
        Log::info('Not working', "this part created part");

        return   parent::handleCustomerSubscriptionCreated($payload);
    }

    public function handleInvoicePaymentSucceeded(array $payload)
    {
        $invoice = $payload['data']['object'];
        $reason = $invoice['billing_reason'] ?? null;

        Log::info('invoice.payment_succeeded received', [
            'invoice_id' => $invoice['id'] ?? null,
            'billing_reason' => $reason,
            'customer' => $invoice['customer'] ?? null,
        ]);

        if (! in_array($reason, ['subscription_create', 'subscription_cycle'], true)) {
            Log::info('invoice.payment_succeeded: skipped, billing_reason not handled', ['reason' => $reason]);
            return $this->successMethod();
        }

        $subscriptionId = $invoice['subscription']
            ?? $invoice['parent']['subscription_details']['subscription']
            ?? null;

        $line = $invoice['lines']['data'][0] ?? [];
        $priceId = $line['price']['id']
            ?? $line['pricing']['price_details']['price']
            ?? null;

        $user = $this->getUserByStripeId($invoice['customer'] ?? null);
        $plan = $priceId ? Plan::where('stripe_price_id', $priceId)->first() : null;

        if (! $user || ! $plan || ! $subscriptionId) {
            Log::warning('invoice.payment_succeeded: stopped, something missing', [
                'user_found' => (bool) $user,
                'plan_found' => (bool) $plan,
                'price_id' => $priceId,
                'subscription_id' => $subscriptionId,
            ]);
            return $this->successMethod();
        }

        $this->recordPayment(
            $user,
            $plan,
            $invoice['id'],
            ($invoice['amount_paid'] ?? 0) / 100,
            strtoupper($invoice['currency'] ?? 'usd'),
            $invoice,
            $subscriptionId
        );

        return $this->successMethod();
    }
    protected function recordPayment(
        User $user,
        Plan $plan,
        string $reference,
        float $amount,
        string $currency,
        array $meta,
        ?string $subscriptionId = null,
    ): void {
        try {
            DB::transaction(function () use ($user, $plan, $reference, $amount, $currency, $meta, $subscriptionId) {
                // The unique tx_ref makes a duplicate fail right here, before any grant exists
                $payment = Payment::create([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'provider' => 'stripe',
                    'tx_ref' => $reference,
                    'amount' => $amount,
                    'currency' => $currency,
                    'status' => 'success',
                    'meta' => $meta,
                    'paid_at' => now(),
                ]);

                $grant = ListingGrant::create([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'listings_used' => 0,
                    'starts_at' => now(),
                    'expires_at' => $plan->duration_days ? now()->addDays($plan->duration_days) : null,
                    'status' => 'active',
                    'stripe_subscription_id' => $subscriptionId,
                ]);

                $payment->update(['listing_grant_id' => $grant->id]);
            });

            Log::info('Stripe payment recorded, grant issued', ['reference' => $reference, 'plan' => $plan->key]);
        } catch (UniqueConstraintViolationException $e) {
            Log::info('Stripe payment already recorded, ignoring duplicate', ['reference' => $reference]);
        }
    }
    public function handleCustomerSubscriptionDeleted(array $payload)
    {
        parent::handleCustomerSubscriptionDeleted($payload);

        $subscription = $payload['data']['object'];

        ListingGrant::where('stripe_subscription_id', $subscription['id'])
            ->where('status', 'active')
            ->update(['status' => 'cancelled']);

        return $this->successMethod();
    }
}
