<?php

namespace App\States\PromptInteraction;

use App\Entities\PromptInteractionsEntity;
use App\States\Interfaces\IState;
use App\ValueObjects\PromptResponse;

class PromptInteractionsContext
{
    private IState $state;

    public function __construct(IState $state)
    {
        $this->state = $state;
    }

    public function setState(IState $state): void
    {
        $this->state = $state;
    }

    public function handleRequest(PromptInteractionsEntity &$promptConversationEntity): ?PromptResponse
    {
        return $this->state->handle($this, $promptConversationEntity);
    }
}
