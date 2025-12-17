<?php

namespace App\Services;

use Illuminate\Support\Str;

class ConversationAgent
{
    public function __construct(
        protected AIService $ai,
        protected ConversationMemoryService $memory
    ) {}

    /**
     * Agent-in tək giriş nöqtəsi
     * HTTP / WebSocket / Queue fərqi yoxdur
     */
    public function handle(string $sessionId, string $message): AgentResponse
    {
        // System prompt
        $messages = [
            [
                'role' => 'system',
                'content' => config('ai.system_prompt'),
            ],
        ];

        //Long-term summary 
        $summary = $this->memory->getSummary($sessionId);

        if (!blank($summary)) {
            $messages[] = [
                'role' => 'system',
                'content' => 'Əvvəlki söhbətin xülasəsi: ' . $summary,
            ];
        }

        //Short-term memory (token limit history)
        $historyMessages = $this->memory->getHistory($sessionId);
        $messages = array_merge($messages, $historyMessages);

        // Current user message
        $messages[] = [
            'role' => 'user',
            'content' => $message,
        ];

        // AI answer
        $answer = $this->ai->ask($messages);

        if (blank($answer)) {
            return AgentResponse::failed('Texniki problem baş verdi');
        }

        // Conversation save
        $this->memory->store($sessionId, $message, $answer);

        // Summary update
        if ($this->memory->shouldSummarize($sessionId)) {
            $newSummary = $this->ai->summarize([
                ['role' => 'system', 'content' => 'Əvvəlki xülasə: ' . $summary],
                ...$historyMessages,
            ]);

            if (!blank($newSummary)) {
                $this->memory->store(
                    $sessionId,
                    '[SUMMARY]',
                    '[UPDATED]',
                    $newSummary
                );
            }
        }

        return AgentResponse::success($sessionId, $answer);
    }
}
