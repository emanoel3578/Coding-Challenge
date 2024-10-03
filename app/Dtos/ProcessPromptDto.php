<?php

namespace App\Dtos;

class ProcessPromptDto extends BaseDto
{
    public function __construct(
        public int $userId,
        public array $promptsTextData,
        public ?int $interactionsId = null,
    ) {
    }
}
