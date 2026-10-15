<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeUser extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $plainPassword,
    ) {}

    public function build(): static
    {
        $tenantName = tenant('name') ?? config('app.name');
        $loginUrl = route('tenant.login');

        return $this->subject("Welcome to {$tenantName}")
            ->view('emails.welcome-user')
            ->with([
                'user' => $this->user,
                'password' => $this->plainPassword,
                'tenantName' => $tenantName,
                'loginUrl' => $loginUrl,
            ]);
    }
}
