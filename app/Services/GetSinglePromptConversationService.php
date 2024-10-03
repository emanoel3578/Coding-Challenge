<?php

namespace App\Services;

use App\Entities\PromptInteractionsEntity;
use App\Exceptions\UserNotFoundException;
use App\Repositories\Interfaces\IPromptInteractionsHistoryRepository;
use App\Repositories\Interfaces\IUserRepository;
use App\Services\Interfaces\IGetSinglePromptConversationService;

class GetSinglePromptConversationService implements IGetSinglePromptConversationService
{
    public function __construct(
        private IPromptInteractionsHistoryRepository $promptInteractionsHistoryRepository,
        private IUserRepository $userRepository
    ) {
    
    }

    public function execute(int $userId, int $interactionsId): PromptInteractionsEntity
    {
        try {
            if (!$this->userRepository->userExists($userId)) {
                throw new UserNotFoundException;
            }

            $promptInteractionsEntity = new PromptInteractionsEntity($interactionsId, $userId);
            $previousInteractions = $this->promptInteractionsHistoryRepository->getPromptInteractionsHistoryById($interactionsId);
            if (!empty($previousInteractions)) {
                $promptInteractionsEntity->addBatchPromptInteractions($previousInteractions);
            }

            return $promptInteractionsEntity;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
