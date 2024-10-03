<?php

use App\Enums\OpenAiInteractionsRoleEnum;
use App\Enums\PromptTypesEnum;
use App\Models\PromptInteractions;
use App\Models\PromptInteractionsHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Inertia\Testing\AssertableInertia;

uses()->group('prompts');

function seedPromptInteractionWithHistory($userId, $amount = 1): array
{
    $promptInteractionIds = [];
    for ($i = 0; $i < $amount; $i++) {
        $promptInteraction = PromptInteractions::factory()->create([
            'user_id' => $userId,
        ]);

        $olderRecordSequence = new Sequence(
            ['role' => OpenAiInteractionsRoleEnum::SYSTEM->value, 'type' => null, 'content' => 'base prompt'],
            ['role' => OpenAiInteractionsRoleEnum::USER->value, 'type' => PromptTypesEnum::QUESTION->value, 'content' => 'question ?'],
            [ 'role' => OpenAiInteractionsRoleEnum::ASSISTANT->value, 'type' => null, 'content' => 'answer'],
        );

        PromptInteractionsHistory::factory()
        ->state($olderRecordSequence)
        ->create([
            'user_id' => $userId,
            'prompt_interaction_id' => $promptInteraction->id,
        ]);

        $promptInteractionIds[] = $promptInteraction->id;
    }

    return $promptInteractionIds;
}

test('Get all prompts conversations with default filters successfully', function () {
    $user = User::factory()->create();
    $amount = 2;
    seedPromptInteractionWithHistory($user->id, $amount);

    $this->actingAs($user)
        ->get(route('prompts.list'))
        ->assertInertia(fn ($assert) => $assert
            ->component('Prompts/HomePrompts')
            ->has('cards', $amount)
        );
});

test('Get all prompts conversations with limit', function () {
    $user = User::factory()->create();
    $amount = 3;
    $limit = 2;
    $lastRecentMessages = 1;
    seedPromptInteractionWithHistory($user->id, $amount);

    $data = [
        'limit' => $limit,
        'order_by' => 'desc',
        'last_recent_messages' => $lastRecentMessages,
    ];

    $this->actingAs($user)
        ->get(route('prompts.list', $data))
        ->assertInertia(
            function (AssertableInertia $assert) use ($limit) {
                $assert->component('Prompts/HomePrompts')
                ->has('cards', $limit, fn ($card) =>
                    $card->has('conversation')
                    ->has('prompt_interaction_id')
                    ->has('created_at')
                );
            }
        );
});
