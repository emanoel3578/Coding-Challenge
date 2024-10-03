<?php

namespace App\Services\Interfaces;

use App\Dtos\StorePromptConversationDto;
use App\Dtos\StorePromptConversationOutputDto;

interface IStorePromptConversationService
{
    public function execute(StorePromptConversationDto $storePromptConversationDto): StorePromptConversationOutputDto;
}
