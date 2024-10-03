<?php

namespace Database\Factories;

use App\Enums\OpenAiInteractionsRoleEnum;
use App\Enums\PromptTypesEnum;
use App\Models\PromptInteractions;
use App\Models\PromptInteractionsHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PromptInteractionsHistory>
 */
class PromptInteractionsHistoryFactory extends Factory
{

    protected $model = PromptInteractionsHistory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'prompt_interaction_id' => PromptInteractions::factory(),
            'content' => $this->faker->sentence,
            'role' => $this->faker->randomElement([
                OpenAiInteractionsRoleEnum::USER->value,
                OpenAiInteractionsRoleEnum::ASSISTANT->value,
                OpenAiInteractionsRoleEnum::SYSTEM->value,
            ]),
            'type' => $this->faker->randomElement([
                PromptTypesEnum::QUESTION->value,
                PromptTypesEnum::MODIFIER->value,
                null
            ]),
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ];
    }
}
