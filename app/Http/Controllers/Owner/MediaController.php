<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Intervention\Image\Format; // Add this import for v4

class MediaController extends Controller
{
    public function manage()
    {
        return view('owner.media.manage');
    }
    public function index(Request $request)
    {
        $media = $request->user()->media()
            ->withCount('properties')
            ->latest()
            ->paginate(30);

        return response()->json($media->through(fn($m) => [
            'id'              => $m->id,
            'url'             => $m->url,
            'name'            => $m->original_name,
            'properties_count' => $m->properties_count,
        ]));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'photos'   => ['required', 'array', 'max:5'],
                'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:20480'], // 20MB = 20480 KB
            ]);

            $uploadedItems = [];

            foreach ($request->file('photos') as $file) {
                $path = 'media/' . $request->user()->id . '/' . uniqid() . '.webp';

                // Decode & Scale down using Intervention Image v4
                $image = Image::decode($file);
                $image->scaleDown(width: 1920, height: 1920);
                $encodedImage = $image->encodeUsingFormat(Format::WEBP, quality: 80);

                Storage::disk('public')->put($path, (string) $encodedImage);
                $sizeKb = (int) round(strlen((string) $encodedImage) / 1024);

                $media = $request->user()->media()->create([
                    'path'          => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime'          => 'image/webp',
                    'size_kb'       => $sizeKb,
                ]);

                $uploadedItems[] = [
                    'id'   => $media->id,
                    'url'  => $media->url,
                    'name' => $media->original_name,
                ];
            }

            return response()->json($uploadedItems, 201);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Media Upload Failed: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            return response()->json([
                'message' => 'Upload Error: ' . $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ], 500);
        }
    }

    public function destroy(Media $media)
    {
        abort_unless($media->user_id === auth()->id(), 403);

        Storage::disk($media->disk)->delete($media->path);
        $media->delete();

        return response()->json(['ok' => true]);
    }
}
