<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AIService
{
    
    public function ask(array $messages): ?string
    {
        // Messages Responses API formated
        $formatted = [];

        foreach ($messages as $msg) {
            $formatted[] = [
                'role' => $msg['role'],
                'content' => $msg['content'], // string
            ];
        }

        $response = Http::withToken(config('services.openai.key'))
            ->post('https://api.openai.com/v1/responses', [
                'model' => 'gpt-4.1-mini',
                'input' => $formatted,
            ]);

        logger()->info('OpenAI RAW RESPONSE', $response->json());

        if (!$response->successful()) {
            logger()->error('OpenAI error', $response->json());
            return null;
        }

        $json = $response->json();

        // Main answer 
        $contents = data_get($json, 'output.0.content', []);

        $text = optional(
            collect($contents)->firstWhere('type', 'output_text')
        )['text'];

        // Fallback 
        $text = $text ?? data_get($json, 'output_text');

        return blank($text) ? null : $text;
    }

   
     // Long-term memory)
  
    public function summarize(array $messages): ?string
    {
        // Message formated for ai 
        $conversationText = collect($messages)
            ->map(function ($m) {
                return strtoupper($m['role']) . ': ' . $m['content'];
            })
            ->implode("\n");

        $response = Http::withToken(config('services.openai.key'))
            ->post('https://api.openai.com/v1/responses', [
                'model' => 'gpt-4.1-mini',
                'input' => [
                    [
                        'role' => 'system',
                        'content' =>
                            'Aşağıdakı söhbəti qısa, texniki və faktları itirmədən xülasə et.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $conversationText,
                    ],
                ],
            ]);

        if (!$response->successful()) {
            logger()->error('Summary error', $response->json());
            return null;
        }

        return data_get(
            $response->json(),
            'output.0.content.0.text'
        );
    }
}
