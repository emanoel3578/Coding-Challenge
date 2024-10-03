<?php

namespace App\Services\Interfaces;

use App\Dtos\GetPromptConversationDto;
use App\Dtos\GetPromptConversationOutputDto;

interface IGetAllPromptConversationsService
{
    public function execute(GetPromptConversationDto $inputDto): GetPromptConversationOutputDto;
}
