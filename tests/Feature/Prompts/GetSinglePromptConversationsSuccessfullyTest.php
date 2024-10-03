<?php

use App\Models\User;
use App\Models\PromptInteractions;
use App\Models\PromptInteractionsHistory;
use App\Enums\OpenAiInteractionsRoleEnum;
use App\Enums\PromptTypesEnum;
use App\Exceptions\UserNotFoundException;
use App\Repositories\Interfaces\IUserRepository;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery\MockInterface;

uses()->group('prompts');

test('Show conversation without interactionsId returns empty conversation', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('prompts.show'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Prompts/Conversation')
            ->has('conversation', 0)
        );
});

test('Show conversation with valid interactionsId returns conversation history', function () {
    $user = User::factory()->create();
    $promptInteraction = PromptInteractions::factory()->create(['user_id' => $user->id]);

    $olderRecordSequence = new Sequence(
        ['role' => OpenAiInteractionsRoleEnum::SYSTEM->value, 'type' => null, 'content' => 'base prompt'],
        ['role' => OpenAiInteractionsRoleEnum::USER->value, 'type' => PromptTypesEnum::QUESTION->value, 'content' => 'question ?'],
        [ 'role' => OpenAiInteractionsRoleEnum::ASSISTANT->value, 'type' => null, 'content' => 'answer'],
    );

    PromptInteractionsHistory::factory()
    ->count($olderRecordSequence->count())
    ->state($olderRecordSequence)
    ->create([
        'user_id' => $user->id,
        'prompt_interaction_id' => $promptInteraction->id,
    ]);

    $this->actingAs($user)
        ->get(route('prompts.show', $promptInteraction->id))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Prompts/Conversation')
            ->has('conversation', 2)
            ->whereAll([
                'conversation.0.content' => 'question ?',
                'conversation.1.content' => 'answer'
            ])
        );
});

test('Show conversation with non-existent user throws UserNotFoundException', function () {
    $user = User::factory()->create();
    $promptInteraction = PromptInteractions::factory()->create(['user_id' => $user->id]);

    $olderRecordSequence = new Sequence(
        ['role' => OpenAiInteractionsRoleEnum::SYSTEM->value, 'type' => null, 'content' => 'base prompt'],
        ['role' => OpenAiInteractionsRoleEnum::USER->value, 'type' => PromptTypesEnum::QUESTION->value, 'content' => 'question ?'],
        [ 'role' => OpenAiInteractionsRoleEnum::ASSISTANT->value, 'type' => null, 'content' => 'answer'],
    );

    PromptInteractionsHistory::factory()
    ->count($olderRecordSequence->count())
    ->state($olderRecordSequence)
    ->create([
        'user_id' => $user->id,
        'prompt_interaction_id' => $promptInteraction->id,
    ]);

    $this->mock(IUserRepository::class, function (MockInterface $mock) {
        $mock->shouldReceive('userExists')->andReturn(false);
    });

    $this->actingAs($user)
        ->get(route('prompts.show', $promptInteraction->id))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Error')
            ->has('stack')
            ->has('message')
                ->where('message', (new UserNotFoundException)->getMessage())
        );
});

