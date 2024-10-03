<?php

use App\Dtos\StorePromptConversationDto;
use App\Dtos\StorePromptConversationOutputDto;
use App\Exceptions\UserNotFoundException;
use App\Repositories\Interfaces\IPromptInteractionsRepository;
use App\Repositories\Interfaces\IUserRepository;
use App\Services\StorePromptConversationService;

uses()->group('prompts');

beforeEach(function () {

    /** @var IPromptInteractionsRepository $interactionsRepository */
    $this->interactionsRepository = mock(IPromptInteractionsRepository::class);
    /** @var IUserRepository $userRepository */
    $this->userRepository = mock(IUserRepository::class);

    // Create the service instance with mocked dependencies
    $this->service = new StorePromptConversationService($this->interactionsRepository, $this->userRepository);
});

test('successfully execute the creating of prompt interaction record when user exists', function () {
    // Arrange
    $dto = new StorePromptConversationDto(userId: 1);
    $expectedInteractionsId = 100;

    // Set up user repository mock to return true (user exists)
    $this->userRepository
        ->shouldReceive('userExists')
        ->with($dto->userId)
        ->andReturn(true);

    // Set up interactions repository mock to return the expected interactions ID
    $this->interactionsRepository
        ->shouldReceive('createPromptInteraction')
        ->with($dto->userId)
        ->andReturn($expectedInteractionsId);

    // Act
    $result = $this->service->execute($dto);

    // Assert
    expect($result)->toBeInstanceOf(StorePromptConversationOutputDto::class);
    expect($result->interactionsId)->toBe($expectedInteractionsId);
});

test('throws UserNotFoundException when user does not exist', function () {
    // Arrange
    $dto = new StorePromptConversationDto(userId: 1);

    // Set up user repository mock to return false (user does not exist)
    $this->userRepository
        ->shouldReceive('userExists')
        ->with($dto->userId)
        ->andReturn(false);

    // Act & Assert
    expect(fn() => $this->service->execute($dto))
        ->toThrow(UserNotFoundException::class);
});

test('rethrows unexpected exceptions', function () {
    // Arrange
    $dto = new StorePromptConversationDto(userId: 1);

    // Set up user repository to return true (user exists)
    $this->userRepository
        ->shouldReceive('userExists')
        ->with($dto->userId)
        ->andReturn(true);

    // Set up interactions repository to throw an exception
    $this->interactionsRepository
        ->shouldReceive('createPromptInteraction')
        ->andThrow(new \Exception('Unexpected Error'));

    // Act & Assert
    expect(fn() => $this->service->execute($dto))
        ->toThrow(\Exception::class, 'Unexpected Error');
});
