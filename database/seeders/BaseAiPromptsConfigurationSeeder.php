<?php

namespace Database\Seeders;

use App\Enums\BaseAiPromptsConfigurationTypeEnum;
use App\Models\BaseAiPromptsConfiguration;
use Illuminate\Database\Seeder;

class BaseAiPromptsConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        BaseAiPromptsConfiguration::factory()->create([
            'content' => config('open_ai_prompts.initial_question'),
            'type' => BaseAiPromptsConfigurationTypeEnum::INITIAL_QUESTION->value,
            'is_active' => true,
        ]);
    }
}
