<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    public function store(Request $request)
    {
        if (!$request->hasFile('image')) {
            return response()->json(['error' => 'No image uploaded'], 422);
        }

        try {
            $file = $request->file('image');
            $path = $file->store('images/upload', 'public');

            $image = Image::create([
                'path' => $path,
                'name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'imageable_type' => '', // Temporary value
                'imageable_id' => 0 // Temporary value
            ]);

            return response()->json([
                'location' => asset('storage/' . $path)
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Upload failed'], 500);
        }
    }
}
