<?php

namespace App\States\Interfaces;

use App\Entities\PromptInteractionsEntity;
use App\States\PromptInteraction\PromptInteractionsContext;
use App\ValueObjects\PromptResponse;

interface IState
{
    public function handle(PromptInteractionsContext $context, PromptInteractionsEntity &$promptMessages): ?PromptResponse;
}
