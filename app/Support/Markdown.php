<?php

namespace App\Support;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class Markdown
{
    public static function render(?string $text): HtmlString
    {
        return new HtmlString(Str::markdown((string) $text, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]));
    }

    /** Plain text version, for search, meta descriptions and structured data. */
    public static function plain(?string $text): string
    {
        $plain = html_entity_decode(strip_tags((string) static::render($text)), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/', ' ', $plain));
    }
}
