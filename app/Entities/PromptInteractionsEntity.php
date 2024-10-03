<?php

namespace App\Entities;

use App\Enums\OpenAiInteractionsRoleEnum;
use App\Enums\PromptTypesEnum;
use InvalidArgumentException;

class PromptInteractionsEntity
{
    public bool $hasBasePromptAsFirstMessageInConversation = false;

    private array $previousInteractions = [];
    private array $conversationHistory = [];

    public function __construct(private int $id, private int $userId)
    {
        $this->id = $id;
        $this->userId = $userId;
    }

    public function addSinglePromptToConversationHistory(string $role, string $promptText, ?string $type = null): void
    {
        // System messages are not stored in the conversation history
        if ($role === OpenAiInteractionsRoleEnum::SYSTEM->value) {
            $this->hasBasePromptAsFirstMessageInConversation = true;
            return;
        }

        $message = $this->mountValidateMessageData($promptText, $role, $type);
        $this->conversationHistory[] = $message;
    }

    public function addBatchPromptInteractions(array $interactions): void
    {
        $this->previousInteractions = array_merge($this->previousInteractions, $interactions);
        $this->convertInteractionsToConversationMessages();
    }

    public function retrieveConversationHistoryByFormat(string $format = 'default'): mixed
    {
        switch($format) {
        case 'default':
            return $this->conversationHistory;
        case 'json':
            return json_encode($this->conversationHistory);
        default:
            throw new InvalidArgumentException('The provided format is not supported.');
        }
    }

    public function retrieveMostRecentInteractions(int $amount = 4): array
    {
        return array_slice($this->conversationHistory, -$amount);
    }

    public function retrieveUniqueIdentifier(): int
    {
        return $this->id;
    }

    private function mountValidateMessageData(string $content, string $role, ?string $type = null): array
    {
        $message = $this->initiatePromptMessageData();
        $message['type'] = $type;
        $message['role'] = $role;
        $message['content'] = $content;

        $this->validateInteractionData($message);
        return $message;
    }

    private function convertInteractionsToConversationMessages(): void
    {
        foreach ($this->previousInteractions as $interaction) {
            $this->addSinglePromptToConversationHistory($interaction['role'], $interaction['content'], $interaction['type']);
        }
    }

    private function initiatePromptMessageData(): array
    {
        return [
            'user_id' => $this->userId,
            'prompt_interaction_id' => $this->id,
            'content' => '',
            'role' => '',
            'type' => null,
        ];
    }

    private function validateInteractionData(array $interaction): void
    {
        if (!isset($interaction['content']) || !is_string($interaction['content']) || empty($interaction['content'])) {
            throw new InvalidArgumentException('The content must be provided and must be a non-empty string.');
        }

        if (isset($interaction['type'])) {
            if (!is_string($interaction['type']) || empty($interaction['type'])) {
                throw new InvalidArgumentException('If provided, the type must be a non-empty string.');
            }

            if (!PromptTypesEnum::tryFrom($interaction['type'])) {
                throw new InvalidArgumentException('The type must be a valid value from PromptTypesEnum.');
            }
        }

        if (!isset($interaction['role']) || !is_string($interaction['role']) || empty($interaction['role'])) {
            throw new InvalidArgumentException('The role must be provided and must be a non-empty string.');
        }

        if (!OpenAiInteractionsRoleEnum::tryFrom($interaction['role'])) {
            throw new InvalidArgumentException('The role must be a valid value from OpenAiInteractionsTypeEnum.');
        }

        if ($interaction['role'] === OpenAiInteractionsRoleEnum::SYSTEM->value && $this->hasBasePromptAsFirstMessageInConversation) {
            throw new InvalidArgumentException('The base prompt can only be the first message in the conversation.');
        }
    }
}
