<?php

use App\Adapters\OpenAi\OpenAiAdapter;
use App\Enums\OpenAiInteractionsRoleEnum;
use App\Enums\PromptTypesEnum;
use App\Models\BaseAiPromptsConfiguration;
use App\Models\PromptInteractions;
use App\Models\PromptInteractionsHistory;
use App\Models\User;
use App\ValueObjects\PromptResponse;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Mockery\MockInterface;

uses()->group('prompts');

test('Send question and process successfully to conversation prompt with previous history', function () {
    $openAiMockResponse = 'The weather is sunny today.';
    $userQuestion = 'What is the weather like today?';
    $modifierQuestion = 'Translate to Spanish';
    $modifierAnswer = 'El clima es soleado hoy.';

    $this->partialMock(OpenAiAdapter::class, function (MockInterface $mock) use ($openAiMockResponse, $modifierAnswer) {
        $mock->shouldReceive('sendRequest')->once()->andReturn(new PromptResponse($openAiMockResponse));
        $mock->shouldReceive('sendRequest')->once()->andReturn(new PromptResponse($modifierAnswer));
    });

    $user = User::factory()->create();
    $promptInteraction = PromptInteractions::factory()->create([
        'user_id' => $user->id,
    ]);

    $olderRecordSequence = new Sequence(
        ['role' => OpenAiInteractionsRoleEnum::SYSTEM->value, 'type' => null, 'content' => 'base prompt'],
        ['role' => OpenAiInteractionsRoleEnum::USER->value, 'type' => PromptTypesEnum::QUESTION->value, 'content' => 'question ?'],
        [ 'role' => OpenAiInteractionsRoleEnum::ASSISTANT->value, 'type' => null, 'content' => 'answer']
    );

    $olderPromptInteractionHistoryRecords = PromptInteractionsHistory::factory()
    ->count(2)
    ->state($olderRecordSequence)
    ->create([
        'user_id' => $user->id,
        'prompt_interaction_id' => $promptInteraction->id,
    ]);

    $data = [
        'interactions_id' => $promptInteraction->id,
        'question_text' => $userQuestion,
        'modifier_text' => $modifierQuestion
    ];

    $response = $this
        ->actingAs($user)
        ->post('/prompts/process', $data);

    $response->assertRedirect();

    $this->assertDatabaseCount('prompt_interactions_history', 6);

    $this->assertDatabaseHas('prompt_interactions_history', [
        'user_id' => $user->id,
        'prompt_interaction_id' => $promptInteraction->id,
        'content' => $olderPromptInteractionHistoryRecords[0]->content,
        'role' => $olderPromptInteractionHistoryRecords[0]->role,
        'type' => $olderPromptInteractionHistoryRecords[0]->type,
    ]);

    $this->assertDatabaseHas('prompt_interactions_history', [
        'user_id' => $user->id,
        'prompt_interaction_id' => $promptInteraction->id,
        'content' => $olderPromptInteractionHistoryRecords[1]->content,
        'role' => $olderPromptInteractionHistoryRecords[1]->role,
        'type' => $olderPromptInteractionHistoryRecords[1]->type,
    ]);

    $this->assertDatabaseHas('prompt_interactions_history', [
        'user_id' => $user->id,
        'prompt_interaction_id' => $promptInteraction->id,
        'content' => $userQuestion,
        'role' => OpenAiInteractionsRoleEnum::USER->value,
        'type' => PromptTypesEnum::QUESTION->value,
    ]);

    $this->assertDatabaseHas('prompt_interactions_history', [
        'user_id' => $user->id,
        'prompt_interaction_id' => $promptInteraction->id,
        'content' => $openAiMockResponse,
        'role' => OpenAiInteractionsRoleEnum::ASSISTANT->value,
        'type' => null,
    ]);

    $this->assertDatabaseHas('prompt_interactions_history', [
        'user_id' => $user->id,
        'prompt_interaction_id' => $promptInteraction->id,
        'content' => $modifierQuestion,
        'role' => OpenAiInteractionsRoleEnum::USER->value,
        'type' => PromptTypesEnum::MODIFIER->value,
    ]);

    $this->assertDatabaseHas('prompt_interactions_history', [
        'user_id' => $user->id,
        'prompt_interaction_id' => $promptInteraction->id,
        'content' => $modifierAnswer,
        'role' => OpenAiInteractionsRoleEnum::ASSISTANT->value,
        'type' => null,
    ]);
});

test('Send question and process successfully to conversation prompt without previous history', function () {
    $openAiMockResponse = 'The weather is sunny today.';
    $userQuestion = 'What is the weather like today?';
    $modifierQuestion = 'Translate to Spanish';
    $modifierAnswer = 'El clima es soleado hoy.';
    $basePromptText = 'base prompt';

    $this->partialMock(OpenAiAdapter::class, function (MockInterface $mock) use ($openAiMockResponse, $modifierAnswer) {
        $mock->shouldReceive('sendRequest')->once()->andReturn(new PromptResponse($openAiMockResponse));
        $mock->shouldReceive('sendRequest')->once()->andReturn(new PromptResponse($modifierAnswer));
    });

    $user = User::factory()->create();
    $promptInteraction = PromptInteractions::factory()->create([
        'user_id' => $user->id,
    ]);

    $basePromptConfiguration = BaseAiPromptsConfiguration::factory()->create(
        [
            'content' => $basePromptText,
            'type' => 'initial_question',
            'is_active' => true
        ]
    );

    $data = [
        'interactions_id' => $promptInteraction->id,
        'question_text' => $userQuestion,
        'modifier_text' => $modifierQuestion
    ];

    $response = $this
        ->actingAs($user)
        ->post('/prompts/process', $data);

    $response->assertRedirect();

    $this->assertDatabaseCount('prompt_interactions_history', 5);

    $this->assertDatabaseHas('prompt_interactions_history', [
        'user_id' => $user->id,
        'prompt_interaction_id' => $promptInteraction->id,
        'content' => $basePromptConfiguration->content,
        'role' => OpenAiInteractionsRoleEnum::SYSTEM->value,
        'type' => null,
    ]);

    $this->assertDatabaseHas('prompt_interactions_history', [
        'user_id' => $user->id,
        'prompt_interaction_id' => $promptInteraction->id,
        'content' => $userQuestion,
        'role' => OpenAiInteractionsRoleEnum::USER->value,
        'type' => PromptTypesEnum::QUESTION->value,
    ]);

    $this->assertDatabaseHas('prompt_interactions_history', [
        'user_id' => $user->id,
        'prompt_interaction_id' => $promptInteraction->id,
        'content' => $openAiMockResponse,
        'role' => OpenAiInteractionsRoleEnum::ASSISTANT->value,
        'type' => null,
    ]);

    $this->assertDatabaseHas('prompt_interactions_history', [
        'user_id' => $user->id,
        'prompt_interaction_id' => $promptInteraction->id,
        'content' => $modifierQuestion,
        'role' => OpenAiInteractionsRoleEnum::USER->value,
        'type' => PromptTypesEnum::MODIFIER->value,
    ]);

    $this->assertDatabaseHas('prompt_interactions_history', [
        'user_id' => $user->id,
        'prompt_interaction_id' => $promptInteraction->id,
        'content' => $modifierAnswer,
        'role' => OpenAiInteractionsRoleEnum::ASSISTANT->value,
        'type' => null,
    ]);
});

test('Send question and create a new conversation successfully from zero history', function () {
    $openAiMockResponse = 'The weather is sunny today.';
    $userQuestion = 'What is the weather like today?';
    $basePromptText = 'base prompt';

    $this->partialMock(OpenAiAdapter::class, function (MockInterface $mock) use ($openAiMockResponse) {
        $mock->shouldReceive('sendRequest')->once()->andReturn(new PromptResponse($openAiMockResponse));
    });

    $user = User::factory()->create();

    $basePromptConfiguration = BaseAiPromptsConfiguration::factory()->create(
        [
            'content' => $basePromptText,
            'type' => 'initial_question',
            'is_active' => true
        ]
    );

    $data = [
        'question_text' => $userQuestion,
        'interactions_id' => null,
        'modifier_text' => null
    ];

    $response = $this
        ->actingAs($user)
        ->post('/prompts/process', $data);

    $response->assertRedirect();

    $newlyCreatedPromptInteractionId = PromptInteractions::where('user_id', $user->id)->first()->id ?? null;

    if (!$newlyCreatedPromptInteractionId) {
        $this->fail('Prompt interaction was not created.');
    }

    $this->assertDatabaseCount('prompt_interactions', 1);
    $this->assertDatabaseCount('prompt_interactions_history', 3);

    $this->assertDatabaseHas('prompt_interactions_history', [
        'user_id' => $user->id,
        'prompt_interaction_id' => $newlyCreatedPromptInteractionId,
        'content' => $basePromptConfiguration->content,
        'role' => OpenAiInteractionsRoleEnum::SYSTEM->value,
        'type' => null,
    ]);

    $this->assertDatabaseHas('prompt_interactions_history', [
        'user_id' => $user->id,
        'prompt_interaction_id' => $newlyCreatedPromptInteractionId,
        'content' => $userQuestion,
        'role' => OpenAiInteractionsRoleEnum::USER->value,
        'type' => PromptTypesEnum::QUESTION->value,
    ]);

    $this->assertDatabaseHas('prompt_interactions_history', [
        'user_id' => $user->id,
        'prompt_interaction_id' => $newlyCreatedPromptInteractionId,
        'content' => $openAiMockResponse,
        'role' => OpenAiInteractionsRoleEnum::ASSISTANT->value,
        'type' => null,
    ]);
});
