<?php

namespace App\Dtos;

class GetPromptConversationOutputDto extends BaseDto
{
    public function __construct(public array $conversationMessages)
    {
    }
}
