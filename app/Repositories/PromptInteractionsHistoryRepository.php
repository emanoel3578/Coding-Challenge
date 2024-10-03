<?php

namespace App\Repositories;

use App\Dtos\GetPromptConversationDto;
use App\Dtos\SavePromptDto;
use App\Enums\OpenAiInteractionsRoleEnum;
use App\Models\PromptInteractionsHistory;
use App\Repositories\Interfaces\IPromptInteractionsHistoryRepository;

class PromptInteractionsHistoryRepository implements IPromptInteractionsHistoryRepository
{
    private PromptInteractionsHistory $model;

    public function __construct()
    {
        $this->model = new PromptInteractionsHistory();
    }

    public function savePromptInteractionHistory(SavePromptDto $savePromptDto): bool
    {
        $data = $savePromptDto->toArray();

        $validData = [
            'user_id' => $data['userId'],
            'prompt_interaction_id' => $data['interactionsId'],
            'content' => $data['content'],
            'type' => $data['type'],
            'role' => $data['role'],
        ];

        $promptInteractionHistory = new PromptInteractionsHistory($validData);

        return $promptInteractionHistory->save();
    }

    public function getPromptInteractionsHistoryById(int $interactionsId): array
    {
        return $this->model
            ->where('prompt_interaction_id', $interactionsId)
            ->get()
            ->toArray();
    }

    public function getPromptInteractionsHistoryByUserId(GetPromptConversationDto $inputDto): array
    {
        return $this->model
            ->where('user_id', $inputDto->userId)
            ->get()
            ->toArray();
    }
}
