<?php

namespace App\Repositories\Interfaces;

use App\Dtos\GetPromptConversationDto;

interface IPromptInteractionsRepository
{
    public function createPromptInteraction(int $userId): int;
    public function promptInteractionExists(int $id): bool;
    public function getPromptInteractionsByUserId(GetPromptConversationDto $getPromptConversationDto): array;
}
