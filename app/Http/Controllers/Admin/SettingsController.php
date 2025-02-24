<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        return view('admin.settings.index');
    }

    public function update(Request $request)
    {
        // Validate settings
        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'site_description' => 'nullable|string',
            'site_creator' => 'nullable|string|max:255',
            'site_logo' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
            'site_favicon' => 'nullable|image|mimes:jpg,jpeg,png,ico|max:1024',
            'site_og_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'site_meta_keywords' => 'nullable|string|max:255',
            'cache_lifetime' => 'required|integer|min:0',
            'site_path_title' => 'nullable|array',
            'site_path_title.*' => 'array',
            'site_path_title.*.path' => 'required|string',
            'site_path_title.*.title' => 'required|string',
            'site_path_description' => 'nullable|array',
            'site_path_description.*.description' => 'nullable|string',
        ]);

        // Handle file uploads
        if ($request->hasFile('site_logo')) {
            $validated['site_logo'] = $request->file('site_logo')->store('settings', 'public');
        }
        if ($request->hasFile('site_favicon')) {
            $validated['site_favicon'] = $request->file('site_favicon')->store('settings', 'public');
        }
        if ($request->hasFile('site_og_image')) {
            $validated['site_og_image'] = $request->file('site_og_image')->store('settings', 'public');
        }

        // Handle cache enabled setting
        setting(['cache_enabled' => $request->has('cache_enabled')]);

        // Process path-specific meta settings
        $pathTitles = [];
        $pathDescriptions = [];

        if ($request->has('site_path_title')) {
            foreach ($request->site_path_title as $index => $data) {
                if (!empty($data['path']) && !empty($data['title'])) {
                    $pathTitles[] = [$data['path'] => $data['title']];

                    if (isset($request->site_path_description[$index]['description'])) {
                        $pathDescriptions[] = [$data['path'] => $request->site_path_description[$index]['description']];
                    }
                }
            }
        }

        setting(['site_path_title' => json_encode($pathTitles)]);
        setting(['site_path_description' => json_encode($pathDescriptions)]);

        // Save other settings
        foreach ($validated as $key => $value) {
            if (!in_array($key, ['site_path_title', 'site_path_description'])) {
                setting([$key => $value]);
            }
        }

        return back()->with('success', 'Cập nhật cài đặt thành công');
    }
}
