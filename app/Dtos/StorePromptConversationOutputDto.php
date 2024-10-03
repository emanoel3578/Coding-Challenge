<?php

namespace App\Dtos;

class StorePromptConversationOutputDto extends BaseDto
{
    public function __construct(public int $interactionsId)
    {
    }
}
