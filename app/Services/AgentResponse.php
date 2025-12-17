<?php

namespace App\Services;

class AgentResponse
{
    public function __construct(
        public bool $success,
        public ?string $sessionId = null,
        public ?string $answer = null,
        public ?string $error = null,
        public array $meta = []
    ) {}

 
    // Success answer
 
    public static function success(string $sessionId, string $answer, array $meta = []): self
    {
        return new self(
            success: true,
            sessionId: $sessionId,
            answer: $answer,
            error: null,
            meta: $meta
        );
    }


    // Failed answer
 
    public static function failed(string $error, array $meta = []): self
    {
        return new self(
            success: false,
            sessionId: null,
            answer: null,
            error: $error,
            meta: $meta
        );
    }


    // JSON format

    public function toArray(): array
    {
        return [
            'success'    => $this->success,
            'session_id' => $this->sessionId,
            'answer'     => $this->answer,
            'error'      => $this->error,
            'meta'       => $this->meta,
        ];
    }
}
