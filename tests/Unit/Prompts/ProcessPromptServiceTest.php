<?php

use App\Adapters\Interfaces\IAiRequestAdapter;
use App\Dtos\ProcessPromptDto;
use App\Dtos\ProcessPromptOutputDto;
use App\Enums\BaseAiPromptsConfigurationTypeEnum;
use App\Enums\OpenAiInteractionsRoleEnum;
use App\Enums\PromptTypesEnum;
use App\Exceptions\ErrorSavingPromptException;
use App\Exceptions\UserNotFoundException;
use App\Repositories\BaseAiPromptsConfigurationRepository;
use App\Repositories\Interfaces\IBaseAiPromptsConfigurationRepository;
use App\Repositories\Interfaces\IPromptInteractionsHistoryRepository;
use App\Repositories\Interfaces\IPromptInteractionsRepository;
use App\Repositories\Interfaces\IUserRepository;
use App\Services\GetSinglePromptConversationService;
use App\Services\ProcessPromptService;
use App\States\PromptInteraction\InitialQuestionState;
use App\States\PromptInteraction\PromptInteractionsContext;
use App\ValueObjects\PromptResponse;
use Mockery\MockInterface;

uses()->group('prompts');

test('executes process prompt successfully with user question, modifier input and previous history', function () {
    $openAiQuestionResponse = 'OpenAI response text';
    $openAiModifierResponse = 'OpenAI Modifier response text';
    $userId = 1;
    $interactionsId = 2;
    $userQuestionText = 'User question';
    $userModifierText = 'User modifier';
    $openAiConversationHistoryMock = [
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

    /** @var IAiRequestAdapter|Mockery\MockInterface $openAiAdapterMock */
    $openAiAdapterMock = mock(IAiRequestAdapter::class, function (MockInterface $mock) use ($openAiQuestionResponse, $openAiModifierResponse) {
        $mock->shouldReceive('sendRequest')
            ->once()
            ->andReturn(new PromptResponse($openAiQuestionResponse));

        $mock->shouldReceive('sendRequest')
            ->once()
            ->andReturn(new PromptResponse($openAiModifierResponse));
    });

    /** @var IUserRepository|Mockery\MockInterface $userRepositoryMock */
    $userRepositoryMock = mock(IUserRepository::class, function (MockInterface $mock) use ($userId) {
        $mock->shouldReceive('userExists')
            ->with($userId)
            ->twice()
            ->andReturn(true);
    });

    /** @var IPromptInteractionsRepository|Mockery\MockInterface $promptInteractionsRepositoryMock */
    $promptInteractionsRepositoryMock = mock(IPromptInteractionsRepository::class, function (MockInterface $mock) use ($interactionsId) {
        $mock->shouldReceive('promptInteractionExists')
            ->with($interactionsId)
            ->times(4)
            ->andReturn(true);
        $mock->shouldReceive('createPromptInteraction')->never();
    });

    /** @var IPromptInteractionsHistoryRepository|Mockery\MockInterface $promptInteractionsHistoryRepositoryMock */
    $promptInteractionsHistoryRepositoryMock = mock(IPromptInteractionsHistoryRepository::class, function (MockInterface $mock) use ($openAiConversationHistoryMock, $interactionsId){
        $mock->shouldReceive('savePromptInteractionHistory')
            ->times(4)
            ->andReturn(true);

        $mock->shouldReceive('getPromptInteractionsHistoryById')
            ->with($interactionsId)
            ->once()
            ->andReturn($openAiConversationHistoryMock);
    });

    /** @var IBaseAiPromptsConfigurationRepository|Mockery\MockInterface $baseAiPromptsConfigurationRepositoryMock */
    $baseAiPromptsConfigurationRepositoryMock = mock(IBaseAiPromptsConfigurationRepository::class, function (MockInterface $mock) {
        $mock->shouldReceive('getActiveConfigurationByType')->never();
    });

    $initialState = new InitialQuestionState($openAiAdapterMock);
    $promptInteractionsContext = new PromptInteractionsContext($initialState);

    $getSinglePromptConversationService = new GetSinglePromptConversationService(
        $promptInteractionsHistoryRepositoryMock,
        $userRepositoryMock
    );

    $service = new ProcessPromptService(
        $promptInteractionsContext,
        $userRepositoryMock,
        $getSinglePromptConversationService,
        $promptInteractionsRepositoryMock,
        $promptInteractionsHistoryRepositoryMock,
        $baseAiPromptsConfigurationRepositoryMock
    );

    $promptDto = new ProcessPromptDto(
        userId: $userId,
        interactionsId: $interactionsId,
        promptsTextData: [
            ['text' => $userQuestionText, 'type' => PromptTypesEnum::QUESTION->value],
            ['text' => $userModifierText, 'type' => PromptTypesEnum::MODIFIER->value]
        ]
    );

    // Execute the service method
    $output = $service->execute($promptDto);

    $expectedConversationOutput = [
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
        [
            "user_id" => $userId,
            "prompt_interaction_id" => $interactionsId,
            "content" => "User question",
            "role" => "user",
            "type" => "question"
        ],
        [
            "user_id" => $userId,
            "prompt_interaction_id" => $interactionsId,
            "content" => "OpenAI response text",
            "role" => "assistant",
            "type" => null
        ],
        [
            "user_id" => $userId,
            "prompt_interaction_id" => $interactionsId,
            "content" => "User modifier",
            "role" => "user",
            "type" => "modifier"
        ],
        [
            "user_id" => $userId,
            "prompt_interaction_id" => $interactionsId,
            "content" => "OpenAI Modifier response text",
            "role" => "assistant",
            "type" => null
        ],
    ];

    // Assert the response
    expect($output)->toBeInstanceOf(ProcessPromptOutputDto::class);
    expect($output->conversationMessages)->toBe($expectedConversationOutput);
});

test('executes process prompt successfully with user question without modifier and without previous conversation history', function () {
    $userQuestionText = 'User question';
    $openAiQuestionResponse = 'OpenAI response text';
    $userId = 1;
    $interactionsId = 2;

    /** @var IAiRequestAdapter|Mockery\MockInterface $openAiAdapterMock */
    $openAiAdapterMock = mock(IAiRequestAdapter::class, function (MockInterface $mock) use ($openAiQuestionResponse) {
        $mock->shouldReceive('sendRequest')
            ->once()
            ->andReturn(new PromptResponse($openAiQuestionResponse));
    });

    /** @var IUserRepository|Mockery\MockInterface $userRepositoryMock */
    $userRepositoryMock = mock(IUserRepository::class, function (MockInterface $mock) use ($userId) {
        $mock->shouldReceive('userExists')
            ->with($userId)
            ->twice()
            ->andReturn(true);
    });

    /** @var IPromptInteractionsRepository|Mockery\MockInterface $promptInteractionsRepositoryMock */
    $promptInteractionsRepositoryMock = mock(IPromptInteractionsRepository::class, function (MockInterface $mock) use ($interactionsId, $userId) {
        $mock->shouldReceive('promptInteractionExists')
            ->with($interactionsId)
            ->twice()
            ->andReturn(true);
        $mock->shouldReceive('createPromptInteraction')
            ->with($userId)
            ->once()
            ->andReturn($interactionsId);
    });

    /** @var IPromptInteractionsHistoryRepository|Mockery\MockInterface $promptInteractionsHistoryRepositoryMock */
    $promptInteractionsHistoryRepositoryMock = mock(IPromptInteractionsHistoryRepository::class, function (MockInterface $mock) use ($interactionsId){
        $mock->shouldReceive('savePromptInteractionHistory')
            ->times(3)
            ->andReturn(true);

        $mock->shouldReceive('getPromptInteractionsHistoryById')
            ->with($interactionsId)
            ->once()
            ->andReturn([]);
    });

    /** @var IBaseAiPromptsConfigurationRepository|Mockery\MockInterface $baseAiPromptsConfigurationRepositoryMock */
    $baseAiPromptsConfigurationRepositoryMock = mock(IBaseAiPromptsConfigurationRepository::class, function (MockInterface $mock) {
        $mock->shouldReceive('getActiveConfigurationByType')
        ->once()
        ->with(BaseAiPromptsConfigurationTypeEnum::INITIAL_QUESTION->value)
        ->andReturn(['content' => 'system text']);
    });

    $initialState = new InitialQuestionState($openAiAdapterMock);
    $promptInteractionsContext = new PromptInteractionsContext($initialState);

    $getSinglePromptConversationService = new GetSinglePromptConversationService(
        $promptInteractionsHistoryRepositoryMock,
        $userRepositoryMock
    );

    $service = new ProcessPromptService(
        $promptInteractionsContext,
        $userRepositoryMock,
        $getSinglePromptConversationService,
        $promptInteractionsRepositoryMock,
        $promptInteractionsHistoryRepositoryMock,
        $baseAiPromptsConfigurationRepositoryMock
    );

    $promptDto = new ProcessPromptDto(
        userId: $userId,
        promptsTextData: [
            ['text' => $userQuestionText, 'type' => PromptTypesEnum::QUESTION->value]
        ],
        interactionsId: null,
    );

    // Execute the service method
    $output = $service->execute($promptDto);

    $expectedConversationOutput = [
        [
            "user_id" => $userId,
            "prompt_interaction_id" => $interactionsId,
            "content" => $userQuestionText,
            "role" => OpenAiInteractionsRoleEnum::USER->value,
            "type" => PromptTypesEnum::QUESTION->value
        ],
        [
            "user_id" => $userId,
            "prompt_interaction_id" => $interactionsId,
            "content" => $openAiQuestionResponse,
            "role" => OpenAiInteractionsRoleEnum::ASSISTANT->value,
            "type" => null
        ]
    ];

    // Assert the response
    expect($output)->toBeInstanceOf(ProcessPromptOutputDto::class);
    expect($output->conversationMessages)->toBe($expectedConversationOutput);
});

test('executes process prompt successfully with user question with modifier and without previous conversation history', function () {
    $openAiQuestionResponse = 'OpenAI response text';
    $openAiModifierResponse = 'OpenAI Modifier response text';
    $userId = 1;
    $interactionsId = 2;
    $userQuestionText = 'User question';
    $userModifierText = 'User modifier';

    /** @var IAiRequestAdapter|Mockery\MockInterface $openAiAdapterMock */
    $openAiAdapterMock = mock(IAiRequestAdapter::class, function (MockInterface $mock) use ($openAiQuestionResponse, $openAiModifierResponse) {
        $mock->shouldReceive('sendRequest')
            ->once()
            ->andReturn(new PromptResponse($openAiQuestionResponse));

        $mock->shouldReceive('sendRequest')
            ->once()
            ->andReturn(new PromptResponse($openAiModifierResponse));
    });

    /** @var IUserRepository|Mockery\MockInterface $userRepositoryMock */
    $userRepositoryMock = mock(IUserRepository::class, function (MockInterface $mock) use ($userId) {
        $mock->shouldReceive('userExists')
            ->with($userId)
            ->twice()
            ->andReturn(true);
    });

    /** @var IPromptInteractionsRepository|Mockery\MockInterface $promptInteractionsRepositoryMock */
    $promptInteractionsRepositoryMock = mock(IPromptInteractionsRepository::class, function (MockInterface $mock) use ($interactionsId, $userId) {
        $mock->shouldReceive('promptInteractionExists')
            ->with($interactionsId)
            ->times(4)
            ->andReturn(true);
        $mock->shouldReceive('createPromptInteraction')
            ->with($userId)
            ->once()
            ->andReturn($interactionsId);
    });

    /** @var IPromptInteractionsHistoryRepository|Mockery\MockInterface $promptInteractionsHistoryRepositoryMock */
    $promptInteractionsHistoryRepositoryMock = mock(IPromptInteractionsHistoryRepository::class, function (MockInterface $mock) use ($interactionsId) {
        $mock->shouldReceive('savePromptInteractionHistory')
            ->times(5)
            ->andReturn(true);

        $mock->shouldReceive('getPromptInteractionsHistoryById')
            ->with($interactionsId)
            ->once()
            ->andReturn([]);
    });

    /** @var IBaseAiPromptsConfigurationRepository|Mockery\MockInterface $baseAiPromptsConfigurationRepositoryMock */
    $baseAiPromptsConfigurationRepositoryMock = mock(IBaseAiPromptsConfigurationRepository::class, function (MockInterface $mock) {
        $mock->shouldReceive('getActiveConfigurationByType')
        ->once()
        ->with(BaseAiPromptsConfigurationTypeEnum::INITIAL_QUESTION->value)
        ->andReturn(['content' => 'system text']);
    });

    $getSinglePromptConversationService = new GetSinglePromptConversationService(
        $promptInteractionsHistoryRepositoryMock,
        $userRepositoryMock
    );

    $initialState = new InitialQuestionState($openAiAdapterMock);
    $promptInteractionsContext = new PromptInteractionsContext($initialState);

    $service = new ProcessPromptService(
        $promptInteractionsContext,
        $userRepositoryMock,
        $getSinglePromptConversationService,
        $promptInteractionsRepositoryMock,
        $promptInteractionsHistoryRepositoryMock,
        $baseAiPromptsConfigurationRepositoryMock
    );

    $promptDto = new ProcessPromptDto(
        userId: $userId,
        promptsTextData: [
            ['text' => $userQuestionText, 'type' => PromptTypesEnum::QUESTION->value],
            ['text' => $userModifierText, 'type' => PromptTypesEnum::MODIFIER->value]
        ],
        interactionsId: null
    );

    // Execute the service method
    $output = $service->execute($promptDto);

    $expectedConversationOutput = [
        [
            "user_id" => $userId,
            "prompt_interaction_id" => $interactionsId,
            "content" => $userQuestionText,
            "role" => OpenAiInteractionsRoleEnum::USER->value,
            "type" => PromptTypesEnum::QUESTION->value
        ],
        [
            "user_id" => $userId,
            "prompt_interaction_id" => $interactionsId,
            "content" => $openAiQuestionResponse,
            "role" => OpenAiInteractionsRoleEnum::ASSISTANT->value,
            "type" => null
        ],
        [
            "user_id" => $userId,
            "prompt_interaction_id" => $interactionsId,
            "content" => $userModifierText,
            "role" => OpenAiInteractionsRoleEnum::USER->value,
            "type" => PromptTypesEnum::MODIFIER->value
        ],
        [
            "user_id" => $userId,
            "prompt_interaction_id" => $interactionsId,
            "content" => $openAiModifierResponse,
            "role" => OpenAiInteractionsRoleEnum::ASSISTANT->value,
            "type" => null
        ],
    ];

    // Assert the response
    expect($output)->toBeInstanceOf(ProcessPromptOutputDto::class);
    expect($output->conversationMessages)->toBe($expectedConversationOutput);
});

test('throws UserNotFoundException if user does not exist', function () {
    $userId = 1;

    /** @var IUserRepository|Mockery\MockInterface $userRepositoryMock */
    $userRepositoryMock = mock(IUserRepository::class, function (MockInterface $mock) use ($userId) {
        $mock->shouldReceive('userExists')->with($userId)->once()->andReturn(false);
    });

    /** @var IPromptInteractionsRepository|Mockery\MockInterface $promptInteractionsRepositoryMock */
    $promptInteractionsRepositoryMock = mock(IPromptInteractionsRepository::class, function (MockInterface $mock) {
        $mock->shouldReceive('promptInteractionExists')->never();
    });

    /** @var IPromptInteractionsHistoryRepository|Mockery\MockInterface $promptInteractionsHistoryRepositoryMock */
    $promptInteractionsHistoryRepositoryMock = mock(IPromptInteractionsHistoryRepository::class, function (MockInterface $mock) {
        $mock->shouldReceive('getPromptInteractionsHistoryById')->never();
        $mock->shouldReceive('savePromptInteractionHistory')->never();
    });

    /** @var PromptInteractionsContext */
    $promptInteractionsContextMock = mock(PromptInteractionsContext::class);

    /** @var GetSinglePromptConversationService */
    $getSinglePromptConversationService = mock(GetSinglePromptConversationService::class);

    /** @var IBaseAiPromptsConfigurationRepository|Mockery\MockInterface $baseAiPromptsConfigurationRepositoryMock */
    $baseAiPromptsConfigurationRepositoryMock = mock(IBaseAiPromptsConfigurationRepository::class, function (MockInterface $mock) {
        $mock->shouldReceive('getActiveConfigurationByType')->never();
    });

    $service = new ProcessPromptService(
        $promptInteractionsContextMock,
        $userRepositoryMock,
        $getSinglePromptConversationService,
        $promptInteractionsRepositoryMock,
        $promptInteractionsHistoryRepositoryMock,
        $baseAiPromptsConfigurationRepositoryMock
    );

    $promptDto = new ProcessPromptDto(
        userId: $userId,
        interactionsId: 1,
        promptsTextData: [
            ['text' => 'User question', 'type' => PromptTypesEnum::QUESTION->value]
        ]
    );

    // Expect the exception
    expect(fn() => $service->execute($promptDto))
        ->toThrow(UserNotFoundException::class);
});

test('throws ErrorSavingPromptException if saving user prompt fails', function () {
    $userId = 1;
    $interactionsId = 2;

    /** @var IUserRepository|Mockery\MockInterface $userRepositoryMock */
    $userRepositoryMock = mock(IUserRepository::class, function (MockInterface $mock) use ($userId) {
        $mock->shouldReceive('userExists')->with($userId)->twice()->andReturn(true);
    });

    /** @var IPromptInteractionsRepository|Mockery\MockInterface $promptInteractionsRepositoryMock */
    $promptInteractionsRepositoryMock = mock(IPromptInteractionsRepository::class, function (MockInterface $mock) use ($interactionsId, $userId) {
        $mock->shouldReceive('promptInteractionExists')->with($interactionsId)->once()->andReturn(true);
        $mock->shouldReceive('createPromptInteraction')->with($userId)->once()->andReturn($interactionsId);
    });

    /** @var IPromptInteractionsHistoryRepository|Mockery\MockInterface $promptInteractionsHistoryRepositoryMock */
    $promptInteractionsHistoryRepositoryMock = mock(IPromptInteractionsHistoryRepository::class, function (MockInterface $mock) {
        $mock->shouldReceive('getPromptInteractionsHistoryById')->once()->andReturn([]);
        $mock->shouldReceive('savePromptInteractionHistory')->once()->andReturn(true);
        $mock->shouldReceive('savePromptInteractionHistory')->once()->andReturn(false);
    });

    /** @var PromptInteractionsContext */
    $promptInteractionsContextMock = mock(PromptInteractionsContext::class);

    /** @var IBaseAiPromptsConfigurationRepository|Mockery\MockInterface $baseAiPromptsConfigurationRepositoryMock */
    $baseAiPromptsConfigurationRepositoryMock = mock(IBaseAiPromptsConfigurationRepository::class, function (MockInterface $mock) {
        $mock->shouldReceive('getActiveConfigurationByType')->once();
    });

    $getSinglePromptConversationService = new GetSinglePromptConversationService(
        $promptInteractionsHistoryRepositoryMock,
        $userRepositoryMock
    );

    $service = new ProcessPromptService(
        $promptInteractionsContextMock,
        $userRepositoryMock,
        $getSinglePromptConversationService,
        $promptInteractionsRepositoryMock,
        $promptInteractionsHistoryRepositoryMock,
        $baseAiPromptsConfigurationRepositoryMock
    );

    $promptDto = new ProcessPromptDto(
        userId: $userId,
        promptsTextData: [
            ['text' => 'User question', 'type' => PromptTypesEnum::QUESTION->value]
        ],
        interactionsId: null,
    );

    // Expect exception
    expect(fn() => $service->execute($promptDto))
        ->toThrow(ErrorSavingPromptException::class);
});

test('throws ErrorSavingPromptException if saving AI response fails', function () {
    $userId = 1;
    $interactionsId = 2;
    $openAiQuestionResponse = 'OpenAI response text';

    /** @var IUserRepository|Mockery\MockInterface $userRepositoryMock */
    $userRepositoryMock = mock(IUserRepository::class, function (MockInterface $mock) use ($userId) {
        $mock->shouldReceive('userExists')->with($userId)->twice()->andReturn(true);
    });

    /** @var IAiRequestAdapter|Mockery\MockInterface $openAiAdapterMock */
    $openAiAdapterMock = mock(IAiRequestAdapter::class, function (MockInterface $mock) use ($openAiQuestionResponse) {
        $mock->shouldReceive('sendRequest')
            ->once()
            ->andReturn(new PromptResponse($openAiQuestionResponse));
    });

    /** @var IPromptInteractionsRepository|Mockery\MockInterface $promptInteractionsRepositoryMock */
    $promptInteractionsRepositoryMock = mock(IPromptInteractionsRepository::class, function (MockInterface $mock) use ($interactionsId, $userId) {
        $mock->shouldReceive('promptInteractionExists')->with($interactionsId)->twice()->andReturn(true);
        $mock->shouldReceive('createPromptInteraction')->with($userId)->once()->andReturn($interactionsId);
    });

    /** @var IPromptInteractionsHistoryRepository|Mockery\MockInterface $promptInteractionsHistoryRepositoryMock */
    $promptInteractionsHistoryRepositoryMock = mock(IPromptInteractionsHistoryRepository::class, function (MockInterface $mock) {
        $mock->shouldReceive('getPromptInteractionsHistoryById')->once()->andReturn([]);
        $mock->shouldReceive('savePromptInteractionHistory')->once()->andReturn(true);
        $mock->shouldReceive('savePromptInteractionHistory')->once()->andReturn(true);
        $mock->shouldReceive('savePromptInteractionHistory')->once()->andReturn(false);
    });

    /** @var IBaseAiPromptsConfigurationRepository|Mockery\MockInterface $baseAiPromptsConfigurationRepositoryMock */
    $baseAiPromptsConfigurationRepositoryMock = mock(IBaseAiPromptsConfigurationRepository::class, function (MockInterface $mock) {
        $mock->shouldReceive('getActiveConfigurationByType')
        ->once()
        ->with(BaseAiPromptsConfigurationTypeEnum::INITIAL_QUESTION->value)
        ->andReturn(['content' => 'system text']);
    });

    $initialState = new InitialQuestionState($openAiAdapterMock);
    $promptInteractionsContext = new PromptInteractionsContext($initialState);

    $getSinglePromptConversationService = new GetSinglePromptConversationService(
        $promptInteractionsHistoryRepositoryMock,
        $userRepositoryMock
    );

    $service = new ProcessPromptService(
        $promptInteractionsContext,
        $userRepositoryMock,
        $getSinglePromptConversationService,
        $promptInteractionsRepositoryMock,
        $promptInteractionsHistoryRepositoryMock,
        $baseAiPromptsConfigurationRepositoryMock
    );

    $promptDto = new ProcessPromptDto(
        userId: $userId,
        promptsTextData: [
            ['text' => 'User question', 'type' => PromptTypesEnum::QUESTION->value]
        ],
        interactionsId: null,
    );

    // Expect exception
    expect(fn() => $service->execute($promptDto))
        ->toThrow(ErrorSavingPromptException::class);
});
