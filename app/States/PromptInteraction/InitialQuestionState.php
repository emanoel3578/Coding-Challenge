<?php

namespace App\States\PromptInteraction;

use App\Adapters\Interfaces\IAiRequestAdapter;
use App\Entities\PromptInteractionsEntity;
use App\Enums\OpenAiInteractionsRoleEnum;
use App\States\Interfaces\IState;
use App\ValueObjects\PromptResponse;

class InitialQuestionState implements IState
{
    public function __construct(private IAiRequestAdapter $openAiAdapter)
    {
    }

    public function handle(PromptInteractionsContext $context, PromptInteractionsEntity &$promptConversationEntity): ?PromptResponse
    {
        $context->setState(new ModifierState($this->openAiAdapter));

        $promptMessages = $promptConversationEntity->retrieveConversationHistoryByFormat();

        $openAiAnswer = $this->openAiAdapter->sendRequest($promptMessages);

        $promptConversationEntity->addSinglePromptToConversationHistory(OpenAiInteractionsRoleEnum::ASSISTANT->value, $openAiAnswer->getResponse());

        return $openAiAnswer;
    }
}
