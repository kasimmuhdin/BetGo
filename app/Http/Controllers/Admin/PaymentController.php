<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ListingGrant;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['user', 'plan']);

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('tx_ref', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($provider = $request->input('provider')) {
            $query->where('provider', $provider);
        }

        $payments = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total_revenue_etb' => Payment::where('status', 'success')->where('currency', 'ETB')->sum('amount'),
            'total_revenue_usd' => Payment::where('status', 'success')->where('currency', 'USD')->sum('amount'),
            'success_count' => Payment::where('status', 'success')->count(),
            'pending_count' => Payment::where('status', 'pending')->count(),
            'failed_count' => Payment::where('status', 'failed')->count(),
            'today_count' => Payment::where('status', 'success')->whereDate('paid_at', today())->count(),
        ];

        return view('admin.payments.index', compact('payments', 'stats'));
    }

    public function show(Payment $payment)
    {
        $payment->load(['user', 'plan', 'listingGrant']);

        return view('admin.payments.show', compact('payment'));
    }


    public function approve(Request $request, Payment $payment)
    {
        abort_if($payment->status === 'success', 422, 'This payment is already marked successful.');

        if ($payment->listing_grants_id) {
            return redirect()->route('admin.payments.show', $payment)
                ->with('error', 'This payment is already linked to a listing grant.');
        }

        $plan = $payment->plan;

        $grant = ListingGrant::create([
            'user_id' => $payment->user_id,
            'plan_id' => $plan->id,
            'listings_used' => 0,
            'starts_at' => now(),
            'expires_at' => $plan->duration_days ? now()->addDays($plan->duration_days) : null,
            'status' => 'active',
        ]);

        $payment->update([
            'status' => 'success',
            'listing_grants_id' => $grant->id,
            'paid_at' => $payment->paid_at ?? now(),
            'meta' => array_merge($payment->meta ?? [], [
                'manually_approved_by' => $request->user()->id,
                'manually_approved_at' => now()->toDateTimeString(),
            ]),
        ]);

        Log::warning('Payment manually approved by admin', [
            'payment_id' => $payment->id,
            'tx_ref' => $payment->tx_ref,
            'admin_id' => $request->user()->id,
            'grant_id' => $grant->id,
        ]);

        return redirect()->route('admin.payments.show', $payment)
            ->with('success', 'Payment approved and listing credit granted.');
    }
}
