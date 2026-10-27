<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Webhook extends Model
{
    protected $fillable = ['name', 'url', 'secret', 'events', 'active'];

    protected $casts = ['events' => 'array', 'active' => 'boolean'];

    /**
     * Fire all active webhooks subscribed to an event.
     */
    public static function dispatch(string $event, array $payload): void
    {
        static::where('active', true)
            ->whereJsonContains('events', $event)
            ->each(function ($hook) use ($event, $payload) {
                try {
                    $body = ['event' => $event, 'data' => $payload, 'sent_at' => now()->toIso8601String()];
                    Http::timeout(5)->withHeaders(
                        $hook->secret
                            ? ['X-Webhook-Signature' => hash_hmac('sha256', json_encode($body), $hook->secret)]
                            : []
                    )->post($hook->url, $body);
                } catch (\Throwable $e) {
                    Log::warning("Webhook {$hook->id} failed: " . $e->getMessage());
                }
            });
    }
}
