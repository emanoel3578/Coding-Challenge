<?php

use App\Enums\OpenAiInteractionsRoleEnum;
use App\Enums\PromptTypesEnum;
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
        Schema::create('prompt_interactions_history', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('prompt_interaction_id');
            $table->longText('content');
            $table->enum('role', [OpenAiInteractionsRoleEnum::USER->value, OpenAiInteractionsRoleEnum::ASSISTANT->value, OpenAiInteractionsRoleEnum::SYSTEM->value]);
            $table->enum('type', [PromptTypesEnum::QUESTION->value, PromptTypesEnum::MODIFIER->value])->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prompt_interactions_history');
    }
};
