<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Support\Markdown;

class PageController extends Controller
{
    public function show(Page $page)
    {
        return view('pages.show', [
            'page' => $page,
            'html' => Markdown::render($page->content),
            'contact' => array_filter(config('betgo.contact', [])),
            'social' => array_filter(config('betgo.social', [])),
        ]);
    }
}
