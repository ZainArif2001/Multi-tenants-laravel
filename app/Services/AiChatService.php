<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Post;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AiChatService
{
    /**
     * Answer a visitor question using only THIS tenant's own data.
     */
    public function reply(string $message, array $history = []): string
    {
        $key = config('services.gemini.api_key');

        if (! $key) {
            return "Thanks for reaching out! Our AI assistant is being set up right now — please try again soon or contact us directly.";
        }

        $response = Http::timeout(20)->post(
            sprintf(
                'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s',
                config('services.gemini.model', 'gemini-2.0-flash'),
                $key
            ),
            [
                'system_instruction' => ['parts' => [['text' => $this->systemPrompt()]]],
                'contents' => [
                    ...$history,
                    ['role' => 'user', 'parts' => [['text' => $message]]],
                ],
            ]
        );

        if (! $response->successful()) {
            return "Sorry, I'm having trouble responding right now. Please try again in a moment.";
        }

        return data_get($response->json(), 'candidates.0.content.parts.0.text')
            ?? "Sorry, I couldn't generate a response. Please try again.";
    }

    protected function systemPrompt(): string
    {
        return "You are the friendly AI support assistant for {$this->tenantName()}. "
            ."Answer visitor questions using ONLY the business information below. "
            ."Keep answers short and helpful (2-3 sentences max). "
            ."If the answer isn't in the information, politely say you don't know "
            ."and suggest contacting the business directly.\n\n"
            .$this->knowledge();
    }

    protected function knowledge(): string
    {
        $lines = ["BUSINESS INFORMATION:"];

        $posts = tenant()->hasModule('posts')
            ? Post::where('status', 'published')->latest()->take(20)->get()
            : collect();

        if ($posts->isNotEmpty()) {
            $lines[] = "\nABOUT / POSTS:";
            foreach ($posts as $post) {
                $lines[] = "- {$post->title}: ".Str::limit(strip_tags((string) $post->body), 400);
            }
        }

        $employees = tenant()->hasModule('employees')
            ? Employee::latest()->take(15)->get()
            : collect();

        if ($employees->isNotEmpty()) {
            $lines[] = "\nTEAM:";
            foreach ($employees as $employee) {
                $lines[] = "- {$employee->name}".($employee->position ? " ({$employee->position})" : '');
            }
        }

        return implode("\n", $lines);
    }

    protected function tenantName(): string
    {
        return tenant('name') ?? config('app.name');
    }
}
