<?php

use App\Entities\PromptInteractionsEntity;
use App\Enums\OpenAiInteractionsRoleEnum;
use App\Enums\PromptTypesEnum;
use App\Exceptions\UserNotFoundException;
use App\Repositories\Interfaces\IPromptInteractionsHistoryRepository;
use App\Repositories\Interfaces\IUserRepository;
use App\Services\GetSinglePromptConversationService;

uses()->group('prompts');

beforeEach(function () {
    /** @var IPromptInteractionsHistoryRepository $promptInteractionsHistoryRepository */
    $this->promptInteractionsHistoryRepository = Mockery::mock(IPromptInteractionsHistoryRepository::class);
    /** @var IUserRepository $userRepository */
    $this->userRepository = Mockery::mock(IUserRepository::class);

    /** @var GetSinglePromptConversationService $service */
    $this->service = new GetSinglePromptConversationService(
        $this->promptInteractionsHistoryRepository,
        $this->userRepository
    );
});

test('it retrieves prompt conversation when user exists', function () {
    $userId = 1;
    $interactionsId = 123;
    $previousInteractions = [
        [
            'user_id' => $userId,
            'prompt_interaction_id' => $interactionsId,
            'content' => 'system text',
            'role' => OpenAiInteractionsRoleEnum::SYSTEM->value,
            'type' => null,
        ],
        [
            'user_id' => $userId,
            'prompt_interaction_id' => $interactionsId,
            'content' => 'random user text',
            'role' => OpenAiInteractionsRoleEnum::USER->value,
            'type' => PromptTypesEnum::QUESTION->value,
        ],
        [
            'user_id' => $userId,
            'prompt_interaction_id' => $interactionsId,
            'content' => 'random response assistant text',
            'role' => OpenAiInteractionsRoleEnum::ASSISTANT->value,
            'type' => null,
        ],
    ];

    $this->userRepository
        ->shouldReceive('userExists')
        ->once()
        ->with($userId)
        ->andReturn(true);

    $this->promptInteractionsHistoryRepository
        ->shouldReceive('getPromptInteractionsHistoryById')
        ->once()
        ->with($interactionsId)
        ->andReturn($previousInteractions);

    $result = $this->service->execute($userId, $interactionsId);

    expect($result)->toBeInstanceOf(PromptInteractionsEntity::class);
    expect($result->retrieveConversationHistoryByFormat())->not->toBeEmpty();
});

test('it throws UserNotFoundException when user does not exist', function () {
    $userId = 1;
    $interactionsId = 123;

    $this->userRepository
        ->shouldReceive('userExists')
        ->with($userId)
        ->andReturn(false);

    $this->service->execute($userId, $interactionsId);
})->throws(UserNotFoundException::class);
