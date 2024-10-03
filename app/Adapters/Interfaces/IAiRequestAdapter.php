<?php

namespace App\Adapters\Interfaces;

use App\ValueObjects\PromptResponse;

interface IAiRequestAdapter
{
    public function sendRequest(array $messages): PromptResponse;
}
