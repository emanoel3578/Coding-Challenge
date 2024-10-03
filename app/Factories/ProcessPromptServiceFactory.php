<?php

namespace App\Factories;

use App\Repositories\Interfaces\IBaseAiPromptsConfigurationRepository;
use App\Repositories\PromptInteractionsHistoryRepository;
use App\Repositories\PromptInteractionsRepository;
use App\Repositories\UserRepository;
use App\Services\Interfaces\IGetSinglePromptConversationService;
use App\Services\ProcessPromptService;
use App\States\PromptInteraction\InitialQuestionState;
use App\States\PromptInteraction\PromptInteractionsContext;

class ProcessPromptServiceFactory
{
    public function __construct(
        private PromptInteractionsContext $promptInteractionsContext,
        private InitialQuestionState $initialQuestionState,
        private UserRepository $userRepository,
        private IGetSinglePromptConversationService $getSinglePromptConversationService,
        private PromptInteractionsRepository $promptInteractionsRepository,
        private PromptInteractionsHistoryRepository $promptInteractionsHistoryRepository,
        private IBaseAiPromptsConfigurationRepository $baseAiPromptsConfigurationRepository
    ) {
    
    }

    public function make(): ProcessPromptService
    {
        $this->promptInteractionsContext->setState($this->initialQuestionState);

        return new ProcessPromptService(
            $this->promptInteractionsContext,
            $this->userRepository,
            $this->getSinglePromptConversationService,
            $this->promptInteractionsRepository,
            $this->promptInteractionsHistoryRepository,
            $this->baseAiPromptsConfigurationRepository
        );
    }
}
