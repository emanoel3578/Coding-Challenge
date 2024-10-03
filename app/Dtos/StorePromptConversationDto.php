<?php

namespace App\Dtos;

class StorePromptConversationDto extends BaseDto
{
    public function __construct(public int $userId)
    {
    }
}
