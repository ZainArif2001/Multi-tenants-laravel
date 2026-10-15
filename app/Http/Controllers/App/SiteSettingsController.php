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
        SiteSetting::current()->update($request->validate([
            'tagline' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'contact_email' => 'nullable|email|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
        ]));

        return back()->with('success', 'Site settings saved.');
    }
}
