<?php

namespace App\Http\Controllers;

use App\Models\BannerSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminBannerController extends Controller
{
    /**
     * The 4 image fields. Files are saved in storage/app/public/banner (path stored: banner/xxxx.jpg).
     */
    private const IMAGE_FIELDS = ['logo', 'image_1', 'image_2', 'image_3'];

    /**
     * Show the Banner Settings form.
     */
    public function index()
    {
        $banner = $this->currentBanner();

        return view('admin.banner.index', compact('banner'));
    }

    /**
     * Validate and save the single banner settings record.
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'brand_name' => 'nullable|string|max:60',
            'small_title' => 'nullable|string|max:255',
            'main_title' => 'nullable|string|max:255',
            'highlight_text' => 'nullable|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'button_text' => 'nullable|string|max:255',
            // Must start with "/" or http(s):// so it can never be a "javascript:" link.
            'button_url' => ['nullable', 'string', 'max:255', 'regex:/^(\/|https?:\/\/)/'],
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url:http,https|max:255',
            'include_1' => 'nullable|string|max:255',
            'include_2' => 'nullable|string|max:255',
            'include_3' => 'nullable|string|max:255',
            'include_4' => 'nullable|string|max:255',
            'offer_title' => 'nullable|string|max:255',
            'discount' => 'nullable|numeric|min:0|max:100',
            'offer_suffix' => 'nullable|string|max:255',
            'facebook_url' => 'nullable|url:http,https|max:255',
            'instagram_url' => 'nullable|url:http,https|max:255',
            'youtube_url' => 'nullable|url:http,https|max:255',
            'x_url' => 'nullable|url:http,https|max:255',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'image_1' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'image_2' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'image_3' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ], [
            'button_url.regex' => 'The button URL must start with / (for example /packages) or with http:// or https://.',
        ]);

        // Checkboxes: unchecked boxes send nothing, so read them as true/false.
        $data['show_offer'] = $request->boolean('show_offer');
        $data['is_active'] = $request->boolean('is_active');

        $banner = $this->currentBanner();

        // No new file = keep the existing image. A new file replaces the old one.
        $oldFiles = [];
        foreach (self::IMAGE_FIELDS as $field) {
            unset($data[$field]);

            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('banner', 'public');
                $oldFiles[] = $banner->$field;
            }
        }

        $banner->update($data);

        // Delete the old files only after the new ones are saved.
        foreach (array_filter($oldFiles) as $oldFile) {
            Storage::disk('public')->delete($oldFile);
        }

        return redirect('/admin/banner')->with('success', 'Banner settings saved successfully.');
    }

    /**
     * Get the one banner record. If it does not exist yet, create it with the default values.
     */
    private function currentBanner(): BannerSetting
    {
        return BannerSetting::first() ?? BannerSetting::create([])->refresh();
    }
}
