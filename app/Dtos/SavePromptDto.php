<?php

namespace App\Dtos;

class SavePromptDto extends BaseDto
{
    public function __construct(
        public int $userId,
        public int $interactionsId,
        public string $content,
        public string $role,
        public ?string $type = null,
    ) {
    }
}
