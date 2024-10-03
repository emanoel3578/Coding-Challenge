<?php

use App\Enums\BaseAiPromptsConfigurationTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('base_ai_prompts_configurations', function (Blueprint $table) {
            $table->id();
            $table->longText('content');
            $table->enum('type', [BaseAiPromptsConfigurationTypeEnum::INITIAL_QUESTION->value]);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('base_ai_prompts_configurations');
    }
};
