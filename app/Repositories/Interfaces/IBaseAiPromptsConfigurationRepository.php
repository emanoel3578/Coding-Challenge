<?php

namespace App\Repositories\Interfaces;

interface IBaseAiPromptsConfigurationRepository
{
    public function getActiveConfigurationByType(string $type): array;
}
