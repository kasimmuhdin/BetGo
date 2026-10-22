<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Markdown;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarkdownPreviewController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate(['content' => ['nullable', 'string', 'max:60000']]);

        return response()->json(['html' => (string) Markdown::render($data['content'] ?? '')]);
    }
}
