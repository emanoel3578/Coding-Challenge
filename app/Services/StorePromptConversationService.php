<?php

namespace App\Services;

use App\Dtos\StorePromptConversationDto;
use App\Dtos\StorePromptConversationOutputDto;
use App\Exceptions\UserNotFoundException;
use App\Repositories\Interfaces\IPromptInteractionsRepository;
use App\Repositories\Interfaces\IUserRepository;
use App\Services\Interfaces\IStorePromptConversationService;

class StorePromptConversationService implements IStorePromptConversationService
{
    public function __construct(
        private IPromptInteractionsRepository $interactionsRepository,
        private IUserRepository $userRepository,
    ) {
    
    }

    public function execute(StorePromptConversationDto $storePromptConversationDto): StorePromptConversationOutputDto
    {
        try {
            if (!$this->userRepository->userExists($storePromptConversationDto->userId)) {
                throw new UserNotFoundException;
            }

            $interactionsId = $this->interactionsRepository->createPromptInteraction($storePromptConversationDto->userId);
            return new StorePromptConversationOutputDto($interactionsId);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
