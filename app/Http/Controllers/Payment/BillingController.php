<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $stripeSubscription = $user->subscription('default');
        $plans = Plan::where('is_active', true)->whereNotNull('duration_days')->orderBy('sort_order')->get();
        $activeGrants = $user->activeListingGrants();

        return view('payments.billing', compact('stripeSubscription', 'plans', 'activeGrants'));
    }

    public function cancel(Request $request)
    {
        $subscription = $request->user()->subscription('default');

        abort_unless($subscription && $subscription->active(), 422, 'No active subscription to cancel.');

        $subscription->cancel();

        return redirect()->route('payments.billing')
            ->with('success', 'Your subscription will remain active until the end of this billing period, then stop renewing.');
    }

    public function resume(Request $request)
    {
        $subscription = $request->user()->subscription('default');

        abort_unless($subscription && $subscription->onGracePeriod(), 422, 'Nothing to resume.');

        $subscription->resume();

        return redirect()->route('payments.billing')->with('success', 'Your subscription has been resumed.');
    }

    public function upgrade(Request $request)
    {
        $validated = $request->validate(['plan_key' => ['required', 'exists:plans,key']]);
        $plan = Plan::where('key', $validated['plan_key'])->firstOrFail();
        $subscription = $request->user()->subscription('default');

        abort_unless($subscription && $subscription->active(), 422, 'No active subscription to change.');
        abort_unless($plan->stripe_price_id, 422, 'This plan is not available for card billing.');

        $subscription->swap($plan->stripe_price_id);

        return redirect()->route('payments.billing')->with('success', "Your plan has been changed to {$plan->name}.");
    }
}
