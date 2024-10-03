<?php

namespace Database\Factories;

use App\Enums\BaseAiPromptsConfigurationTypeEnum;
use App\Models\BaseAiPromptsConfiguration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PromptInteractions>
 */
class BaseAiPromptsConfigurationFactory extends Factory
{
    protected $model = BaseAiPromptsConfiguration::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'content' => $this->faker->sentence,
            'type' => $this->faker->randomElement([BaseAiPromptsConfigurationTypeEnum::INITIAL_QUESTION->value]),
            'is_active' => true,
        ];
    }
}
