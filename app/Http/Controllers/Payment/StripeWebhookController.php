<?php

namespace App\Http\Controllers\Payment;

use App\Models\ListingGrant;
use App\Models\Plan;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Http\Controllers\WebhookController;

class StripeWebhookController extends WebhookController
{

    public function handleCheckoutSessionCompleted(array $payload)
    {
        $session = $payload['data']['object'] ?? null;
        $planKey = $session['metadata']['plan_key'] ?? null;
        $user = $this->getUserByStripeId($session['customer']);
        if (!$user || !$planKey) {
            return $this->successMethod();
        }
        $plan = Plan::where('key', $planKey)->first();
        if (!$plan) {
            return $this->successMethod();
        }

        if (empty($session['subscription'])) {
            ListingGrant::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'listings_used' => 0,
                'start_at' => now(),
                'expire_at' => null,
                'status' => 'active',
            ]);

            Log::info('Stripe one-time checkout granted credit', ['user_id' => $user->id, 'plan' => $plan->key]);
        }
        return $this->successMethod();
    }

    public function handleCustomerSubscriptionCreated(array $payload)
    {
        parent::handleCustomerSubscriptionCreated($payload);
        $subscription = $payload['data']['object'] ?? null;
        $user = $this->getUserByStripeId($subscription['customer']);

        $price = $subscription['items']['data'][0]['price']['id'] ?? null;

        $plan = Plan::where('stripe+price_id', $price)->first();

        if ($user && $plan) {
            ListingGrant::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'listings_used' => 0,
                'start_at' => now(),
                'expire_at' => $plan->duration_days ? now()->addDays($plan->duration_days) : null,
                'status' => 'active',
                'stripe_subscription_id' => $subscription['id'] ?? null,
            ]);
            Log::info('stripe subscription created, granted credit', ['user_id' => $user->id, 'plan' => $plan->key, 'subscription_id' => $subscription['id'] ?? null]);
        }

        return $this->successMethod();
    }
    public function handleInvoicePaymentSucceeded(array $payload)
    {
        $invoice = $payload['data']['object'];
        $subscriptionId = $invoice['subscription'] ?? null;

        if (! $subscriptionId || ($invoice['billing_reason'] ?? null) !== 'subscription_cycle') {
            return $this->successMethod();
        }

        $user = $this->getUserByStripeId($invoice['customer']);
        $priceId = $invoice['lines']['data'][0]['price']['id'] ?? null;
        $plan = Plan::where('stripe_price_id', $priceId)->first();

        if ($user && $plan) {
            ListingGrant::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'listings_used' => 0,
                'start_at' => now(),
                'expire_at' => $plan->duration_days ? now()->addDays($plan->duration_days) : null,
                'status' => 'active',
                'stripe_subscription_id' => $subscriptionId,
            ]);

            Log::info('Stripe subscription renewed, new grant issued', ['user_id' => $user->id, 'plan' => $plan->key]);
        }

        return $this->successMethod();
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
