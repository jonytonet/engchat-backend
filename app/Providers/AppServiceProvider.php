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

        // TODO: Adicionar outros bindings conforme necessário
        // MessageRepositoryInterface::class => EloquentMessageRepository::class
        // ChannelRepositoryInterface::class => EloquentChannelRepository::class
        // CategoryRepositoryInterface::class => EloquentCategoryRepository::class
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
