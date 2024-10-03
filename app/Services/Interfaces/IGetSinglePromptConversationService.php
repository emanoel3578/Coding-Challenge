<?php

namespace App\Services\Interfaces;

use App\Entities\PromptInteractionsEntity;

interface IGetSinglePromptConversationService
{
    public function execute(int $userId, int $promptInteractionId): PromptInteractionsEntity;
}
