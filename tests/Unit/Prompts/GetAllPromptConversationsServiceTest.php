<?php

use App\Dtos\GetPromptConversationDto;
use App\Dtos\GetPromptConversationOutputDto;
use App\Enums\OpenAiInteractionsRoleEnum;
use App\Enums\PromptTypesEnum;
use App\Exceptions\UserNotFoundException;
use App\Repositories\Interfaces\IPromptInteractionsRepository;
use App\Repositories\Interfaces\IUserRepository;
use App\Services\GetAllPromptConversationsService;
use Carbon\Carbon;

uses()->group('prompts');

beforeEach(function () {
    /** @var IUserRepository $userRepository */
    $this->userRepository = Mockery::mock(IUserRepository::class);
    /** @var IPromptInteractionsRepository $promptInteractionsRepository */
    $this->promptInteractionsRepository = Mockery::mock(IPromptInteractionsRepository::class);

    $this->service = new GetAllPromptConversationsService(
        $this->promptInteractionsRepository,
        $this->userRepository
    );
});

test('retrieves all prompt conversations for an existing user with exact amount of last recent messages', function () {
    $userId = 1;
    $lastRecentMessages = 1;
    $firstInteractionId = 1;
    $secondInteractionId = 2;
    $inputDto = new GetPromptConversationDto(
        userId: $userId,
        lastRecentMessages: $lastRecentMessages
    );

    $allPromptsInteractions = [
        [
            "id" => $firstInteractionId,
            "user_id" => $userId,
            "created_at" => Carbon::now(),
            "updated_at" => Carbon::now(),
            "deleted_at" => null,
            'prompt_interactions_history' => [
                [
                    'user_id' => $userId,
                    'prompt_interaction_id' => $firstInteractionId,
                    'content' => 'system text',
                    'role' => OpenAiInteractionsRoleEnum::SYSTEM->value,
                    'type' => null,
                ],
                [
                    'user_id' => $userId,
                    'prompt_interaction_id' => $firstInteractionId,
                    'content' => 'random user text',
                    'role' => OpenAiInteractionsRoleEnum::USER->value,
                    'type' => PromptTypesEnum::QUESTION->value,
                ],
                [
                    'user_id' => $userId,
                    'prompt_interaction_id' => $firstInteractionId,
                    'content' => 'random response assistant text',
                    'role' => OpenAiInteractionsRoleEnum::ASSISTANT->value,
                    'type' => null,
                ]
            ]
        ],
        [
            "id" => $secondInteractionId,
            "user_id" => $userId,
            "created_at" => Carbon::now(),
            "updated_at" => Carbon::now(),
            "deleted_at" => null,
            'prompt_interactions_history' => [
                [
                    'user_id' => $userId,
                    'prompt_interaction_id' => $secondInteractionId,
                    'content' => 'system text',
                    'role' => OpenAiInteractionsRoleEnum::SYSTEM->value,
                    'type' => null,
                ],
                [
                    'user_id' => $userId,
                    'prompt_interaction_id' => $secondInteractionId,
                    'content' => 'random user text',
                    'role' => OpenAiInteractionsRoleEnum::USER->value,
                    'type' => PromptTypesEnum::QUESTION->value,
                ],
                [
                    'user_id' => $userId,
                    'prompt_interaction_id' => $secondInteractionId,
                    'content' => 'random response assistant text',
                    'role' => OpenAiInteractionsRoleEnum::ASSISTANT->value,
                    'type' => null,
                ]
            ]
        ],
    ];

    $this->userRepository
        ->shouldReceive('userExists')
        ->with($userId)
        ->andReturn(true);

    $this->promptInteractionsRepository
        ->shouldReceive('getPromptInteractionsByUserId')
        ->with($inputDto)
        ->andReturn($allPromptsInteractions);

    $result = $this->service->execute($inputDto);

    expect($result)->toBeInstanceOf(GetPromptConversationOutputDto::class);
    expect($result->conversationMessages)->toHaveCount(2);
    expect($result->conversationMessages[0]['conversation'])->toBeArray();
    expect($result->conversationMessages[0]['conversation'])->toHaveCount($lastRecentMessages);
    expect($result->conversationMessages[0]['prompt_interaction_id'])->toBe($firstInteractionId);

    expect($result->conversationMessages[1]['conversation'])->toBeArray();
    expect($result->conversationMessages[1]['conversation'])->toHaveCount($lastRecentMessages);
    expect($result->conversationMessages[1]['prompt_interaction_id'])->toBe($secondInteractionId);
});

test('returns an empty list if no conversations exist', function () {
    $userId = 1;
    $lastRecentMessages = 2;
    $inputDto = new GetPromptConversationDto($userId, $lastRecentMessages);

    $this->userRepository
        ->shouldReceive('userExists')
        ->with($userId)
        ->andReturn(true);

    $this->promptInteractionsRepository
        ->shouldReceive('getPromptInteractionsByUserId')
        ->with($inputDto)
        ->andReturn([]);

    $result = $this->service->execute($inputDto);

    expect($result)->toBeInstanceOf(GetPromptConversationOutputDto::class);
    expect($result->conversationMessages)->toBeEmpty();
});

test('throws UserNotFoundException when user does not exist', function () {
    $userId = 1;
    $lastRecentMessages = 2;
    $inputDto = new GetPromptConversationDto($userId, $lastRecentMessages);

    $this->userRepository
        ->shouldReceive('userExists')
        ->with($userId)
        ->andReturn(false);

    $this->service->execute($inputDto);
})->throws(UserNotFoundException::class);
