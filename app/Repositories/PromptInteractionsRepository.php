<?php

namespace App\Repositories;

use App\Dtos\GetPromptConversationDto;
use App\Models\PromptInteractions;
use App\Models\PromptInteractionsHistory;
use App\Repositories\Interfaces\IPromptInteractionsRepository;

class PromptInteractionsRepository implements IPromptInteractionsRepository
{
    private PromptInteractions $model;

    public function __construct()
    {
        $this->model = new PromptInteractions();
    }

    public function createPromptInteraction(int $userId): int
    {
        $promptInteraction = $this->model->create(
            [
            'user_id' => $userId,
            ]
        );

        return $promptInteraction->id;
    }

    public function promptInteractionExists(int $id): bool
    {
        return !empty($this->model->find($id));
    }

    public function getPromptInteractionsByUserId(GetPromptConversationDto $getPromptConversationDto): array
    {
        return $this->model
            ->with('promptInteractionsHistory')
            ->limit($getPromptConversationDto->limit)
            ->orderBy('created_at', $getPromptConversationDto->orderBy)
            ->get()
            ->toArray();
    }
}
