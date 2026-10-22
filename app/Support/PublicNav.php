<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

class PublicNav
{
    /** Single source of truth for the public header and footer links. */
    public static function items(): array
    {
        $home = route('home');

        $items = [
            ['label' => 'Home', 'href' => $home, 'section' => null, 'active' => request()->routeIs('home')],
            ['label' => 'Browse', 'href' => $home . '#listings', 'section' => 'listings', 'active' => false],
            ['label' => 'How it works', 'href' => $home . '#how-it-works', 'section' => 'how-it-works', 'active' => false],
            ['label' => 'Pricing', 'href' => route('pricing'), 'section' => null, 'active' => request()->routeIs('pricing')],
        ];

        // About and Contact show up automatically once the pages route exists.
        if (Route::has('pages.show')) {
            foreach (['about' => 'About', 'contact' => 'Contact'] as $slug => $label) {
                $items[] = [
                    'label' => $label,
                    'href' => route('pages.show', $slug),
                    'section' => null,
                    'active' => request()->is($slug),
                ];
            }
        }

        return $items;
    }
}
