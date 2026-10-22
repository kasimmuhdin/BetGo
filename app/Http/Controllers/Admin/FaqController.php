<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FaqController extends Controller
{
    public function index()
    {
        $items = Faq::ordered()->get()->map(fn(Faq $f) => [
            'id' => $f->id,
            'question' => $f->question,
            'published' => $f->is_published,
            'editUrl' => route('admin.faqs.edit', $f),
            'toggleUrl' => route('admin.faqs.toggle', $f),
            'deleteUrl' => route('admin.faqs.destroy', $f),
        ])->values();

        return view('admin.faqs.index', compact('items'));
    }

    public function create()
    {
        return view('admin.faqs.create', ['faq' => new Faq(['is_published' => true])]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['sort_order'] = (int) Faq::max('sort_order') + 1; // new questions go to the end

        Faq::create($data);

        return redirect()->route('admin.faqs.index')->with('success', 'Question added.');
    }

    public function edit(Faq $faq)
    {
        return view('admin.faqs.edit', compact('faq'));
    }

    public function update(Request $request, Faq $faq)
    {
        $faq->update($this->validated($request));

        return redirect()->route('admin.faqs.index')->with('success', 'Question saved.');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();

        return redirect()->route('admin.faqs.index')->with('success', 'Question deleted.');
    }

    public function toggle(Faq $faq): JsonResponse
    {
        $faq->update(['is_published' => ! $faq->is_published]);

        return response()->json(['published' => $faq->is_published]);
    }

    public function reorder(Request $request): JsonResponse
    {
        $ids = $request->validate([
            'ids' => ['required', 'array', 'max:500'],
            'ids.*' => ['integer', 'distinct', 'exists:faqs,id'],
        ])['ids'];

        // A stale tab can send an old list. Refuse instead of scrambling the order.
        abort_unless(count($ids) === Faq::count(), 409, 'The list changed. Reload the page.');

        DB::transaction(function () use ($ids) {
            foreach ($ids as $position => $id) {
                Faq::whereKey($id)->update(['sort_order' => $position + 1]);
            }
        });

        return response()->json(['ok' => true]);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'min:5', 'max:255'],
            'answer' => ['required', 'string', 'min:2', 'max:5000'],
        ]);

        $data['is_published'] = $request->boolean('is_published');

        return $data;
    }
}
