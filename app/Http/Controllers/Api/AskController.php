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

    $history = Conversation::where('session_id', $sessionId)
        ->orderBy('id', 'desc')
        ->take(5)
        ->get(['question', 'answer'])
        ->reverse(); // xronoloji düzülüş

    $messages = [];

    $messages = [
    [
        'role' => 'system',
        'content' => 'Sən Azərbaycan dilində danışan, backend və Laravel üzrə AI köməkçisən.'
    ]
];

// əvvəlki history
foreach ($history as $row) {
    $messages[] = ['role' => 'user', 'content' => $row->question];
    $messages[] = ['role' => 'assistant', 'content' => $row->answer];
}

// indiki mesaj
$messages[] = [
    'role' => 'user',
    'content' => $request->input('message')
];

    $answer = $ai->ask($messages);

        if (blank($answer)) {
        return response()->json([
            'session_id' => $sessionId,
            'answer' => 'Texniki problem baş verdi'
        ]);
    }

    Conversation::create([
        'session_id' => $sessionId,
        'question'   => $request->input('message'),
        'answer'     => $answer,
    ]);

    return response()->json([
        'session_id' => $sessionId,
        'answer'     => $answer,
    ]);
}



}