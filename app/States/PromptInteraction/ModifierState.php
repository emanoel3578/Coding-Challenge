<?php

namespace App\States\PromptInteraction;

use App\Adapters\Interfaces\IAiRequestAdapter;
use App\Entities\PromptInteractionsEntity;
use App\Enums\OpenAiInteractionsRoleEnum;
use App\States\Interfaces\IState;
use App\ValueObjects\PromptResponse;

class ModifierState implements IState
{
    public function __construct(private IAiRequestAdapter $openAiAdapter)
    {
    }

    public function handle(PromptInteractionsContext $context, PromptInteractionsEntity &$promptConversationEntity): ?PromptResponse
    {
        $promptMessages = $promptConversationEntity->retrieveConversationHistoryByFormat();

        $promptResponseWithModifierAnswer = $this->openAiAdapter->sendRequest($promptMessages);
        $promptConversationEntity->addSinglePromptToConversationHistory(OpenAiInteractionsRoleEnum::ASSISTANT->value, $promptResponseWithModifierAnswer->getResponse());

        return $promptResponseWithModifierAnswer;
    }
}
