<?php

namespace App\ValueObjects;

class PromptResponse
{
    public function __construct(public string $response)
    {
        $this->response = $response;
    }

    public function getResponse(): string
    {
        return $this->response;
    }
}
