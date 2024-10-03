<?php

namespace App\Services\Interfaces;

use App\Dtos\ProcessPromptDto;
use App\Dtos\ProcessPromptOutputDto;

interface IProcessPromptService
{
    public function execute(ProcessPromptDto $promptDto): ProcessPromptOutputDto;
}
