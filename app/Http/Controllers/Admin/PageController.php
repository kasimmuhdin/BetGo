<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index()
    {
        return view('admin.pages.index', [
            'pages' => Page::with('editor:id,name')->orderBy('id')->get(),
            'faqTotal' => Faq::count(),
            'faqLive' => Faq::published()->count(),
        ]);
    }

    public function edit(Page $page)
    {
        return view('admin.pages.edit', [
            'page' => $page,
            'contact' => config('betgo.contact', []),
            'social' => config('betgo.social', []),
        ]);
    }

    public function update(Request $request, Page $page)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'min:2', 'max:120'],
            'meta_description' => ['nullable', 'string', 'max:160'],
            'content' => ['required', 'string', 'max:60000'],
            'version' => ['required', 'integer'],
        ]);
        abort_unless($request->user()->role === 'admin', 401, "yo arent able to update pages");
        // The form remembers when the page was loaded. If someone saved since, stop instead of overwriting.
        if ((int) $data['version'] !== $page->updated_at->getTimestamp()) {
            return back()->withInput()->with(
                'error',
                'Someone else saved this page while you were editing. Copy your text, reload the page, then try again.'
            );
        }

        $page->update([
            'title' => $data['title'],
            'meta_description' => $data['meta_description'] ?? null,
            'content' => $data['content'],
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.pages.edit', $page)->with('success', "{$page->title} was saved.");
    }
}
