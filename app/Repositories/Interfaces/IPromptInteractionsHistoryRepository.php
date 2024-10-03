<?php

namespace App\Repositories\Interfaces;

use App\Dtos\GetPromptConversationDto;
use App\Dtos\SavePromptDto;

interface IPromptInteractionsHistoryRepository
{
    public function savePromptInteractionHistory(SavePromptDto $savePromptDto): bool;
    public function getPromptInteractionsHistoryById(int $id): array;
    public function getPromptInteractionsHistoryByUserId(GetPromptConversationDto $inputDto): array;
}
