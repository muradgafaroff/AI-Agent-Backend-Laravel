<?php

namespace App\Services;

use App\Models\Conversation;

class ConversationMemoryService
{
    protected int $tokenLimit = 1500;

   
     // Latest summary
   
    public function getSummary(string $sessionId): ?string
    {
        return Conversation::where('session_id', $sessionId)
            ->whereNotNull('summary')
            ->latest('id')
            ->value('summary');
    }


    //Token limit history 

    public function getHistory(string $sessionId): array
    {
        $rows = Conversation::where('session_id', $sessionId)
            ->orderBy('id', 'desc')
            ->get(['question', 'answer']);

        $messages = [];
        $tokens = 0;

        foreach ($rows as $row) {
            $cost = mb_strlen($row->question) + mb_strlen($row->answer);

            if ($tokens + $cost > $this->tokenLimit) {
                break;
            }

            $messages[] = ['role' => 'user', 'content' => $row->question];
            $messages[] = ['role' => 'assistant', 'content' => $row->answer];

            $tokens += $cost;
        }

        return array_reverse($messages);
    }


    // Conversation save edir

    public function store(
        string $sessionId,
        string $question,
        string $answer,
        ?string $summary = null
    ): void {
        Conversation::create([
            'session_id' => $sessionId,
            'question'   => $question,
            'answer'     => $answer,
            'summary'    => $summary,
        ]);
    }


    // Summary update ?

    public function shouldSummarize(string $sessionId, int $after = 10): bool
    {
        return Conversation::where('session_id', $sessionId)->count() >= $after;
    }
}
