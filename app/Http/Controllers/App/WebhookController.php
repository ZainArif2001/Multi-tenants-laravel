<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Webhook;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebhookController extends Controller
{
    public const EVENTS = [
        'contact.received' => 'New contact form submission',
        'employee.created' => 'New employee added',
    ];

    public function index(): View
    {
        return view('app.webhooks.index', [
            'hooks' => Webhook::latest()->get(),
            'events' => self::EVENTS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'url' => 'required|url|max:500',
            'secret' => 'nullable|string|max:100',
            'events' => 'required|array|min:1',
            'events.*' => 'in:' . implode(',', array_keys(self::EVENTS)),
        ]);
        $data['active'] = true;

        Webhook::create($data);

        return back()->with('success', 'Webhook added.');
    }

    public function toggle(Webhook $webhook): RedirectResponse
    {
        $webhook->update(['active' => ! $webhook->active]);

        return back();
    }

    public function destroy(Webhook $webhook): RedirectResponse
    {
        $webhook->delete();

        return back()->with('success', 'Webhook removed.');
    }
}
