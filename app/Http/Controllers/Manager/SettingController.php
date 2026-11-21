<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ProductImageService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit()
    {
        return view('manager.settings', ['s' => collect(Setting::DEFAULTS)->map(fn ($d, $k) => Setting::get($k))->all()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'business_name' => 'required|string|max:80',
            'tagline' => 'nullable|string|max:160',
            'phone' => 'nullable|string|max:40',
            'whatsapp' => 'nullable|string|max:40',
            'email' => 'nullable|email|max:120',
            'address' => 'nullable|string|max:200',
            'website' => 'nullable|url|max:120',
            'default_delivery_fee' => 'required|numeric|min:0|max:100000',
            'express_station_fee' => 'nullable|numeric|min:0|max:100000',
            'invoice_note' => 'nullable|string|max:200',
            'invoice_terms' => 'nullable|string|max:200',
            'helpline' => 'nullable|string|max:40',
            'hours' => 'nullable|string|max:120',
            'whatsapp_channel' => 'nullable|url|max:200',
            'facebook' => 'nullable|url|max:200', 'instagram' => 'nullable|url|max:200', 'x' => 'nullable|url|max:200',
            'youtube' => 'nullable|url|max:200', 'tiktok' => 'nullable|url|max:200',
            'about_text' => 'nullable|string|max:5000', 'delivery_text' => 'nullable|string|max:8000',
            'policy_returns' => 'nullable|string|max:8000', 'policy_terms' => 'nullable|string|max:12000', 'policy_privacy' => 'nullable|string|max:12000',
            'hero_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:8192',
        ]);
        unset($data['hero_image']);
        foreach ($data as $key => $value) {
            Setting::set($key, $value === null ? '' : (string) $value);
        }

        // The big picture behind the home page's headline.
        $images = app(ProductImageService::class);
        if ($request->hasFile('hero_image')) {
            $new = $images->store($request->file('hero_image'), 'site', 2000, 'hero_image');
            $images->deleteFile(Setting::get('hero_image'));
            Setting::set('hero_image', $new);
        } elseif ($request->boolean('remove_hero_image')) {
            $images->deleteFile(Setting::get('hero_image'));
            Setting::set('hero_image', '');
        }
        Setting::set('store_open', $request->boolean('store_open') ? '1' : '0');

        return back()->with('success', 'Settings saved.');
    }
}
