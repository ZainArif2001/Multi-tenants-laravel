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
        return view('app.settings.edit', ['settings' => SiteSetting::current()]);
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
        ]);

        SiteSetting::current()->update(collect($data)->except('primary_color')->all());

        if (array_key_exists('primary_color', $data)) {
            $t = tenant();
            $t->primary_color = $data['primary_color'];
            $t->save();
        }

        return back()->with('success', 'Site settings saved.');
    }
}
