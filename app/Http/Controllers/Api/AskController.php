<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AskRequest;
use App\Services\ConversationAgent;
use Illuminate\Support\Str;

class AskController extends Controller
{
    public function ask(AskRequest $request, ConversationAgent $agent)
    {
        // Session ID (client send  avto create)
        $sessionId = $request->header('X-Session-Id') ?? (string) Str::uuid();

        // Agent 
        $response = $agent->handle(
            sessionId: $sessionId,
            message: $request->input('message')
        );

        // HTTP answer
        return response()->json(
            $response->toArray(),
            $response->success ? 200 : 500
        );
    }
}
