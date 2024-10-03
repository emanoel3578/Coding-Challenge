<?php

namespace App\Dtos;

class GetPromptConversationDto extends BaseDto
{
    public function __construct(
        public int $userId,
        public int $limit = 10,
        public string $orderBy = 'desc',
        public int $lastRecentMessages = 4
    ) {
    
    }
}
