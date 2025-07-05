<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Contracts\ConversationRepositoryInterface;
use App\Repositories\Contracts\ContactRepositoryInterface;
use App\Repositories\Eloquent\EloquentConversationRepository;
use App\Repositories\Eloquent\EloquentContactRepository;
use App\Services\UserPermissionService;
use App\Services\ConversationStatusService;
use App\Services\MessageAttachmentService;
use App\Services\ConversationTransferService;
use App\Services\BotClassificationService;
use App\Services\AutoResponseService;
use App\Services\ErpIntegrationService;
use App\Models\MessageAttachment;
use App\Observers\MessageAttachmentObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Repository bindings - seguindo padrão Dependency Injection obrigatório
        $this->app->bind(
            ConversationRepositoryInterface::class,
            EloquentConversationRepository::class
        );

        $this->app->bind(
            ContactRepositoryInterface::class,
            EloquentContactRepository::class
        );

        // Service bindings - SOLID/DDD compliance
        $this->app->singleton(UserPermissionService::class);
        $this->app->singleton(ConversationStatusService::class);
        $this->app->singleton(MessageAttachmentService::class);
        $this->app->singleton(ConversationTransferService::class);
        $this->app->singleton(BotClassificationService::class);
        $this->app->singleton(AutoResponseService::class);
        $this->app->singleton(ErpIntegrationService::class);

        // TODO: Implementar repositories restantes quando necessário
        // $this->app->bind(
        //     MessageRepositoryInterface::class,
        //     EloquentMessageRepository::class
        // );

        // $this->app->bind(
        //     ChannelRepositoryInterface::class,
        //     EloquentChannelRepository::class
        // );

        // $this->app->bind(
        //     CategoryRepositoryInterface::class,
        //     EloquentCategoryRepository::class
        // );

        // $this->app->bind(
        //     UserRepositoryInterface::class,
        //     EloquentUserRepository::class
        // );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register model observers
        MessageAttachment::observe(MessageAttachmentObserver::class);
    }
}
