<?php

namespace App\Providers;

use App\Adapters\Interfaces\IAiRequestAdapter;
use App\Adapters\OpenAi\OpenAiAdapter;
use App\Repositories\BaseAiPromptsConfigurationRepository;
use App\Repositories\Interfaces\IBaseAiPromptsConfigurationRepository;
use App\Repositories\Interfaces\IPromptInteractionsHistoryRepository;
use App\Repositories\Interfaces\IPromptInteractionsRepository;
use App\Repositories\Interfaces\IUserRepository;
use App\Repositories\PromptInteractionsHistoryRepository;
use App\Repositories\PromptInteractionsRepository;
use App\Repositories\UserRepository;
use App\Services\GetAllPromptConversationsService;
use App\Services\GetSinglePromptConversationService;
use App\Services\Interfaces\IGetAllPromptConversationsService;
use App\Services\Interfaces\IGetSinglePromptConversationService;
use App\Services\Interfaces\IStorePromptConversationService;
use App\Services\StorePromptConversationService;
use App\States\Interfaces\IState;
use App\States\PromptInteraction\InitialQuestionState;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(IAiRequestAdapter::class, OpenAiAdapter::class);
        $this->app->bind(IState::class, InitialQuestionState::class);
        $this->app->bind(IPromptInteractionsRepository::class, PromptInteractionsRepository::class);
        $this->app->bind(IUserRepository::class, UserRepository::class);
        $this->app->bind(IGetAllPromptConversationsService::class, GetAllPromptConversationsService::class);
        $this->app->bind(IStorePromptConversationService::class, StorePromptConversationService::class);
        $this->app->bind(IGetSinglePromptConversationService::class, GetSinglePromptConversationService::class);
        $this->app->bind(IPromptInteractionsHistoryRepository::class, PromptInteractionsHistoryRepository::class);
        $this->app->bind(IBaseAiPromptsConfigurationRepository::class, BaseAiPromptsConfigurationRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
