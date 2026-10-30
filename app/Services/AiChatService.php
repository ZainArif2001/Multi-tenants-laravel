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

    /**
     * Stream a reply token-by-token via Gemini's SSE endpoint.
     * Yields raw text chunks; caller re-emits them to the browser.
     */
    public function streamReply(string $message, array $history = []): \Generator
    {
        $key = config('services.gemini.api_key');

        if (! $key) {
            yield "Thanks for reaching out! Our AI assistant is being set up right now — please try again soon or contact us directly.";

            return;
        }

        $response = Http::timeout(30)->withOptions(['stream' => true])->post(
            sprintf(
                'https://generativelanguage.googleapis.com/v1beta/models/%s:streamGenerateContent?alt=sse&key=%s',
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
            yield "Sorry, I'm having trouble responding right now. Please try again in a moment.";

            return;
        }

        $body = $response->toPsrResponse()->getBody();
        $buffer = '';

        while (! $body->eof()) {
            $buffer .= $body->read(8192);

            while (($pos = strpos($buffer, "\n")) !== false) {
                $line = trim(substr($buffer, 0, $pos));
                $buffer = substr($buffer, $pos + 1);

                if (! str_starts_with($line, 'data: ')) {
                    continue;
                }

                $json = json_decode(substr($line, 6), true);

                foreach ($json['candidates'][0]['content']['parts'] ?? [] as $part) {
                    if (($part['text'] ?? '') !== '') {
                        yield $part['text'];
                    }
                }
            }
        }
    }

    /**
     * Quick-reply chips for the visitor widget — latest posts + a contact nudge.
     */
    public function suggestedQuestions(): array
    {
        $suggestions = tenant()->hasModule('posts')
            ? Post::where('status', 'published')->latest()->take(3)
                ->pluck('title')->map(fn ($t) => 'Tell me about '.$t)->all()
            : [];

        $suggestions[] = 'How can I contact your team?';

        return $suggestions;
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

        $site = \App\Models\SiteSetting::current();
        $about = collect([$site->tagline, $site->description])->filter()->implode("\n");
        if ($about) {
            $lines[] = "\nABOUT THE BUSINESS:\n".$about;
        }
        if ($site->contact_email) {
            $lines[] = "Contact email: {$site->contact_email}";
        }

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
                $lines[] = "- {$employee->name}".($employee->job_title ? " ({$employee->job_title})" : '');
            }
        }

        // Uploaded knowledge-base documents
        foreach (\App\Models\KnowledgeDoc::latest()->take(10)->get() as $doc) {
            $lines[] = "\nDOCUMENT: {$doc->title}\n" . Str::limit($doc->content, 3000);
        }

        return implode("\n", $lines);
    }

    protected function tenantName(): string
    {
        return tenant('name') ?? config('app.name');
    }
}
