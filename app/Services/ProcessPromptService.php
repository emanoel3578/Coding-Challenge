<?php

namespace App\Services;

use App\Dtos\ProcessPromptDto;
use App\Dtos\ProcessPromptOutputDto;
use App\Dtos\SavePromptDto;
use App\Entities\PromptInteractionsEntity;
use App\Enums\BaseAiPromptsConfigurationTypeEnum;
use App\Enums\OpenAiInteractionsRoleEnum;
use App\Enums\PromptTypesEnum;
use App\Exceptions\ErrorSavingPromptException;
use App\Exceptions\PromptInteractionsNotFoundException;
use App\Exceptions\UserNotFoundException;
use App\Repositories\Interfaces\IBaseAiPromptsConfigurationRepository;
use App\Repositories\Interfaces\IPromptInteractionsHistoryRepository;
use App\Repositories\Interfaces\IPromptInteractionsRepository;
use App\Repositories\Interfaces\IUserRepository;
use App\Services\Interfaces\IGetSinglePromptConversationService;
use App\Services\Interfaces\IProcessPromptService;
use App\States\PromptInteraction\PromptInteractionsContext;

class ProcessPromptService implements IProcessPromptService
{
    public function __construct(
        private PromptInteractionsContext $promptInteractionsContext,
        private IUserRepository $userRepository,
        private IGetSinglePromptConversationService $getSinglePromptConversationService,
        private IPromptInteractionsRepository $promptInteractionsRepository,
        private IPromptInteractionsHistoryRepository $promptInteractionsHistoryRepository,
        private IBaseAiPromptsConfigurationRepository $baseAiPromptsConfigurationRepository
    ) {
    
    }

    /**
     * The general idea around this method is to in the case of the current conversation has any
     * previous interactions we can mount the conversation history in order to add as context to the current prompt.
     */
    public function execute(ProcessPromptDto $promptDto): ProcessPromptOutputDto
    {
        try {
            if (!$this->userRepository->userExists($promptDto->userId)) {
                throw new UserNotFoundException;
            }

            // If the prompt does not have an interactionsId, we create a new one.
            if (empty($promptDto->interactionsId)) {
                $promptDto->interactionsId = $this->promptInteractionsRepository->createPromptInteraction($promptDto->userId);
            }

            $promptConversationEntity = $this->getSinglePromptConversationService->execute($promptDto->userId, $promptDto->interactionsId);

            foreach($promptDto->promptsTextData as $promptTextData) {
                if (!$promptTextData['text'] || !$promptTextData['type']) {
                    continue;
                }

                $this->processPromptToHistory($promptConversationEntity, $promptDto, $promptTextData);
            }

            return new ProcessPromptOutputDto($promptConversationEntity->retrieveConversationHistoryByFormat());
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    private function savePromptToDatabase(int $userId, int $interactionsId, string $promptText, string $role, ?string $promptType = null): bool
    {
        if (!$this->promptInteractionsRepository->promptInteractionExists($interactionsId)) {
            throw new PromptInteractionsNotFoundException;
        }

        $savePromptDto = new SavePromptDto(
            $userId,
            $interactionsId,
            $promptText,
            $role,
            $promptType,
        );

        return $this->promptInteractionsHistoryRepository->savePromptInteractionHistory($savePromptDto);
    }

    private function processPromptToHistory(PromptInteractionsEntity &$promptConversationEntity, ProcessPromptDto $promptDto, array $promptTextData): bool
    {
        // If the conversation does not have the initial base prompt, we add it to the conversation history.
        if (!$promptConversationEntity->hasBasePromptAsFirstMessageInConversation) {
            $initialBasePrompt = $this->getInitialBasePrompt();
            $this->promptInteractionsHistoryRepository->savePromptInteractionHistory(
                new SavePromptDto(
                    $promptDto->userId,
                    $promptDto->interactionsId,
                    $initialBasePrompt,
                    OpenAiInteractionsRoleEnum::SYSTEM->value,
                    null,
                )
            );
            $promptConversationEntity->addSinglePromptToConversationHistory(OpenAiInteractionsRoleEnum::SYSTEM->value, $initialBasePrompt);
        }

        // We save the user prompt to the database.
        $savedUserPrompt = $this->savePromptToDatabase(
            $promptDto->userId,
            $promptDto->interactionsId,
            $promptTextData['text'],
            OpenAiInteractionsRoleEnum::USER->value,
            $promptTextData['type'],
        );

        if (!$savedUserPrompt) {
            throw new ErrorSavingPromptException;
        }

        // We add the user prompt to the conversation history.
        $promptConversationEntity->addSinglePromptToConversationHistory(OpenAiInteractionsRoleEnum::USER->value, $promptTextData['text'], $promptTextData['type']);

        // We process the prompt interaction with the AI.
        $openAiResponse = $this->promptInteractionsContext->handleRequest($promptConversationEntity);
        $openAiResponseText = $openAiResponse->getResponse();

        // We add the AI response to the conversation history.
        $savedPromptResponse = $this->savePromptToDatabase($promptDto->userId, $promptDto->interactionsId, $openAiResponseText, OpenAiInteractionsRoleEnum::ASSISTANT->value);

        if (!$savedPromptResponse) {
            throw new ErrorSavingPromptException;
        }

        return $savedPromptResponse;
    }

    private function getInitialBasePrompt(): string
    {
        $initialPromptConfiguration = $this->baseAiPromptsConfigurationRepository->getActiveConfigurationByType(BaseAiPromptsConfigurationTypeEnum::INITIAL_QUESTION->value);

        return $initialPromptConfiguration['content'] ?? '';
    }
}
