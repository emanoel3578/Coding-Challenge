<?php

namespace App\Repositories;

use App\Models\BaseAiPromptsConfiguration;
use App\Repositories\Interfaces\IBaseAiPromptsConfigurationRepository;

class BaseAiPromptsConfigurationRepository implements IBaseAiPromptsConfigurationRepository
{
    private BaseAiPromptsConfiguration $model;

    public function __construct()
    {
        $this->model = new BaseAiPromptsConfiguration();
    }

    public function getActiveConfigurationByType(string $type): array
    {
        $activeConfigurationContent = $this->model
            ->where('type', $type)
            ->where('is_active', true)
            ->first();

        if (empty($activeConfigurationContent)) {
            return [];
        }

        return $activeConfigurationContent->toArray();
    }
}
