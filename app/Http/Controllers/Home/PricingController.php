<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\Plan;

class PricingController extends Controller
{
    public function index()
    {
        $plans = Plan::publicLists();

        return view('home.pricing', [
            'plans'        => $plans,
            'featuredKey'  => config('betgo.featured_plan'),
            'cardPayments' => $plans->contains(fn($p) => $p->price_usd),
            'user'         => auth()->user(),
        ]);
    }
}
