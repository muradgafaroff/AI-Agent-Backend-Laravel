<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AIService
{
    public function ask(array $messages): ?string
    {
        // messages-i Responses API formatına 
        $formatted = [];
       


        foreach ($messages as $msg) {
            $formatted[] = [
                'role' => $msg['role'],
                'content' => $msg['content'],
            ];
        }

        $response = Http::withToken(config('services.openai.key'))
        ->post('https://api.openai.com/v1/responses', [
            'model' => 'gpt-4.1-mini',
            'input' => $formatted,
        ]);

    logger()->info('OpenAI RAW RESPONSE', $response->json());

    if (!$response->successful()) {
        return null;
    }

    $json = $response->json();

    $contents = data_get($json, 'output.0.content', []);

    $text = collect($contents)
        ->firstWhere('type', 'output_text')['text']
        ?? data_get($json, 'output_text');
        return blank($text) ? null : $text;
}
}