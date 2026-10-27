<?php

namespace App\Console\Commands;

use App\Models\ContactMessage;
use App\Models\Employee;
use App\Models\Message;
use App\Models\Post;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TenantDigest extends Command
{
    protected $signature = 'tenant:digest';

    protected $description = 'Email each tenant admin a weekly activity digest.';

    public function handle(): void
    {
        Tenant::all()->each(function ($tenant) {
            tenancy()->initialize($tenant);

            try {
                $stats = [
                    'New posts' => Post::where('created_at', '>=', now()->subWeek())->count(),
                    'Chat messages' => Message::where('created_at', '>=', now()->subWeek())->count(),
                    'Contact submissions' => ContactMessage::where('created_at', '>=', now()->subWeek())->count(),
                    'New employees' => Employee::where('created_at', '>=', now()->subWeek())->count(),
                    'Total users' => User::count(),
                ];

                $body = "Weekly digest for {$tenant->name}\n\n"
                    . collect($stats)->map(fn ($v, $k) => "• {$k}: {$v}")->implode("\n");

                User::role('admin')->pluck('email')->each(fn ($email) =>
                    Mail::raw($body, fn ($m) => $m->to($email)->subject("📊 {$tenant->name} — weekly digest"))
                );

                $this->info("Digest sent for {$tenant->name}");
            } finally {
                tenancy()->end();
            }
        });
    }
}
