<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            ['key' => 'single', 'name' => 'Single Listing', 'price' => 100, 'price_usd' => 1, 'listings_limit' => 1, 'duration_days' => null, 'sort_order' => 1],
            ['key' => 'basic', 'name' => 'Basic Monthly', 'price' => 1000, 'price_usd' => 10, 'listings_limit' => 400, 'duration_days' => 30, 'sort_order' => 2],
            ['key' => 'pro', 'name' => 'Pro Monthly', 'price' => 3000, 'price_usd' => 30, 'listings_limit' => 1500, 'duration_days' => 30, 'sort_order' => 3],
            ['key' => 'unlimited', 'name' => 'Unlimited Monthly', 'price' => 5000, 'price_usd' => 50, 'listings_limit' => null, 'duration_days' => 30, 'sort_order' => 4],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['key' => $plan['key']], $plan);
        }
    }
}
