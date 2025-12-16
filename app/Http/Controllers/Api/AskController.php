<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AskRequest;
use App\Models\Conversation;
use App\Services\AIService;
use Illuminate\Support\Str;

class AskController extends Controller
{
    public function ask(AskRequest $request, AIService $ai)
    {
       
        $sessionId = $request->header('X-Session-Id') ?? (string) Str::uuid();

        $summary = Conversation::where('session_id', $sessionId)
            ->latest()
            ->value('summary');

        $history = Conversation::where('session_id', $sessionId)
            ->orderBy('id', 'desc')
            ->get(['question', 'answer']);

       $systemPrompt = config('ai.system_prompt');



        $messages = [
            [
                'role' => 'system',
                'content' => $systemPrompt,
            ]
        ];


        if (!blank($summary)) {
            $messages[] = [
                'role' => 'system',
                'content' => 'Əvvəlki söhbətin xülasəsi: ' . $summary,
            ];
        }

        $historyMessages = [];
        $tokenLimit = 1500;
        $currentTokens = 0;

        foreach ($history as $row) {
            $tokens = mb_strlen($row->question) + mb_strlen($row->answer);

            if ($currentTokens + $tokens > $tokenLimit) {
                break;
            }

            $historyMessages[] = ['role' => 'user', 'content' => $row->question];
            $historyMessages[] = ['role' => 'assistant', 'content' => $row->answer];

            $currentTokens += $tokens;
        }

        $historyMessages = array_reverse($historyMessages);

        $messages = array_merge(
            $messages,
            $historyMessages,
            [[
                'role' => 'user',
                'content' => $request->input('message'),
            ]]
        );

        $answer = $ai->ask($messages);

        if (blank($answer)) {
            return response()->json([
                'session_id' => $sessionId,
                'answer' => 'Texniki problem baş verdi',
            ]);
        }

        Conversation::create([
            'session_id' => $sessionId,
            'question'   => $request->input('message'),
            'answer'     => $answer,
        ]);

        if ($history->count() >= 10) {
            $newSummary = $ai->summarize([
                ['role' => 'system', 'content' => 'Əvvəlki xülasə: ' . $summary],
                ...$historyMessages
            ]);

            if (is_string($newSummary) && !blank($newSummary)) {
                $lastConversationId = Conversation::where('session_id', $sessionId)
                    ->latest('id')
                    ->value('id');

                if ($lastConversationId) {
                    Conversation::where('id', $lastConversationId)
                        ->update(['summary' => $newSummary]);
                }
            }
        }

        return response()->json([
            'session_id' => $sessionId,
            'answer'     => $answer,
        ]);
    }
}
