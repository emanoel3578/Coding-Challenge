<?php

use App\Entities\PromptInteractionsEntity;
use App\Enums\OpenAiInteractionsRoleEnum;
use App\Enums\PromptTypesEnum;

uses()->group('prompts');

test('adds a single prompt to conversation history', function () {
    $promptInteraction = new PromptInteractionsEntity(1, 1);

    $promptInteraction->addSinglePromptToConversationHistory(
        OpenAiInteractionsRoleEnum::USER->value,
        "What is the weather today?",
        PromptTypesEnum::QUESTION->value
    );

    $expectedHistory = [
        [
            'user_id' => 1,
            'prompt_interaction_id' => 1,
            'content' => "What is the weather today?",
            'role' => OpenAiInteractionsRoleEnum::USER->value,
            'type' => PromptTypesEnum::QUESTION->value,
        ]
    ];

    expect($promptInteraction->retrieveConversationHistoryByFormat())->toEqual($expectedHistory);
    expect(count($promptInteraction->retrieveConversationHistoryByFormat()))->toBe(1);
});

test('adds batch prompt interactions', function () {
    $promptInteraction = new PromptInteractionsEntity(1, 1);

    $batchData = [
        [
            'content' => 'question 1',
            'role' => OpenAiInteractionsRoleEnum::USER->value,
            'type' => PromptTypesEnum::QUESTION->value
        ],
        [
            'content' => 'answer 1',
            'role' => OpenAiInteractionsRoleEnum::ASSISTANT->value,
            'type' => null
        ]
    ];

    $promptInteraction->addBatchPromptInteractions($batchData);

    $expectedHistory = [
        [
            'user_id' => 1,
            'prompt_interaction_id' => 1,
            'content' => 'question 1',
            'role' => OpenAiInteractionsRoleEnum::USER->value,
            'type' => PromptTypesEnum::QUESTION->value,
        ],
        [
            'user_id' => 1,
            'prompt_interaction_id' => 1,
            'content' => 'answer 1',
            'role' => OpenAiInteractionsRoleEnum::ASSISTANT->value,
            'type' => null,
        ],
    ];

    expect($promptInteraction->retrieveConversationHistoryByFormat())->toEqual($expectedHistory);
    expect(count($promptInteraction->retrieveConversationHistoryByFormat()))->toBe(2);
});

test('retrieves conversation history in JSON format', function () {
    $promptInteraction = new PromptInteractionsEntity(1, 1);

    $promptInteraction->addSinglePromptToConversationHistory(
        OpenAiInteractionsRoleEnum::USER->value,
        "Tell me about the universe.",
        PromptTypesEnum::QUESTION->value
    );

    $jsonHistory = $promptInteraction->retrieveConversationHistoryByFormat('json');

    $expectedJson = json_encode([
        [
            'user_id' => 1,
            'prompt_interaction_id' => 1,
            'content' => "Tell me about the universe.",
            'role' => OpenAiInteractionsRoleEnum::USER->value,
            'type' => PromptTypesEnum::QUESTION->value,
        ],
    ]);

    expect($jsonHistory)->toEqual($expectedJson);
});

test('throws an exception for unsupported format during retrieval', function () {
    $this->expectException(InvalidArgumentException::class);

    $promptInteraction = new PromptInteractionsEntity(1, 1);
    $promptInteraction->retrieveConversationHistoryByFormat('unsupported_format');
});

test('throws an exception for invalid interaction data', function ($userId, $promptInteractionId, $content, $role, $type) {
    $this->expectException(InvalidArgumentException::class);

    $promptInteraction = new PromptInteractionsEntity(1, 1);
    $promptInteraction->addSinglePromptToConversationHistory($role, $content, $type);
})->with([
    'empty content' => [1, 1, '', OpenAiInteractionsRoleEnum::USER->value, PromptTypesEnum::QUESTION->value],
    'invalid type' => [1, 1, 'content', OpenAiInteractionsRoleEnum::USER->value, 'invalid_type'],
    'empty role' => [1, 1, 'content', '', PromptTypesEnum::QUESTION->value],
    'invalid role' => [1, 1, 'content', 'invalid_role', PromptTypesEnum::QUESTION->value],
    'invalid type and role' => [1, 1, 'content', 'invalid_role', 'invalid_type'],
]);
