<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteSettingsController extends Controller
{
    public function edit(): View
    {
        if (! tenant()->embed_key) {
            tenant()->embed_key = (string) \Illuminate\Support\Str::uuid();
            tenant()->save();
        }

        return view('app.settings.edit', [
            'settings' => SiteSetting::current(),
            'embedKey' => tenant()->embed_key,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tagline' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'contact_email' => 'nullable|email|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'primary_color' => 'nullable|string|regex:/^#[0-9a-fA-F]{6}$/',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:1024',
            'hero_title' => 'nullable|string|max:120',
            'hero_subtitle' => 'nullable|string|max:500',
            'footer_text' => 'nullable|string|max:300',
        ]);

        $settings = SiteSetting::current();

        $data['show_hero'] = $request->boolean('show_hero');
        $data['show_posts'] = $request->boolean('show_posts');
        $data['show_contact'] = $request->boolean('show_contact');

        if ($request->hasFile('logo')) {
            if ($settings->logo_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($settings->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('site', 'public');
        }

        $settings->update(collect($data)->except(['primary_color', 'logo'])->all());

        if (array_key_exists('primary_color', $data)) {
            $t = tenant();
            $t->primary_color = $data['primary_color'];
            $t->save();
        }

        return back()->with('success', 'Site settings saved.');
    }
}
