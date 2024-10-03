<?php

namespace App\Services;

use App\Dtos\GetPromptConversationDto;
use App\Dtos\GetPromptConversationOutputDto;
use App\Entities\PromptInteractionsEntity;
use App\Exceptions\UserNotFoundException;
use App\Repositories\Interfaces\IPromptInteractionsRepository;
use App\Repositories\Interfaces\IUserRepository;
use App\Services\Interfaces\IGetAllPromptConversationsService;

class GetAllPromptConversationsService implements IGetAllPromptConversationsService
{
    public function __construct(
        private IPromptInteractionsRepository $promptInteractionsRepository,
        private IUserRepository $userRepository
    ) {

    }

    public function execute(GetPromptConversationDto $inputDto): GetPromptConversationOutputDto
    {
        try {
            if (!$this->userRepository->userExists($inputDto->userId)) {
                throw new UserNotFoundException;
            }

            $allPromptsInteractions = $this->promptInteractionsRepository->getPromptInteractionsByUserId($inputDto);
            if (empty($allPromptsInteractions)) {
                return new GetPromptConversationOutputDto([]);
            }

            // We're retrieve a collection of conversations with the most recent messages from the conversation
            $promptInteractionsWithEntity = [];
            foreach($allPromptsInteractions as $conversation) {
                $messages = $conversation['prompt_interactions_history'] ?? [];
                $promptInteractionsEntity = new PromptInteractionsEntity($conversation['id'], $inputDto->userId);
                $promptInteractionsEntity->addBatchPromptInteractions($messages);

                $promptValidatedData['prompt_interaction_id'] = $conversation['id'];
                $promptValidatedData['conversation'] = $promptInteractionsEntity->retrieveMostRecentInteractions($inputDto->lastRecentMessages);
                $promptValidatedData['created_at'] = $conversation['created_at'];
                $promptInteractionsWithEntity[] = $promptValidatedData;
            }

            return new GetPromptConversationOutputDto($promptInteractionsWithEntity);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
