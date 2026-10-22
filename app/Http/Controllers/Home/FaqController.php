<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Support\Markdown;
use Illuminate\Support\Str;

class FaqController extends Controller
{
    public function index()
    {
        $items = Faq::published()->ordered()->get()->map(function (Faq $faq) {
            $plain = Markdown::plain($faq->answer);

            return [
                'question' => $faq->question,
                'html' => Markdown::render($faq->answer),
                'plain' => $plain,
                'search' => Str::lower($faq->question . ' ' . $plain),
            ];
        });

        return view('faq.index', compact('items'));
    }
}
