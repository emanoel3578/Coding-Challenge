<?php

namespace App\Dtos;

class ProcessPromptOutputDto extends BaseDto
{
    public function __construct(
        public array $conversationMessages,
    ) {
    }
}
