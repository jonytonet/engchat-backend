<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Contracts\ConversationRepositoryInterface;
use App\Repositories\Contracts\ContactRepositoryInterface;
use App\Repositories\Eloquent\EloquentConversationRepository;
use App\Repositories\Eloquent\EloquentContactRepository;
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
        //
    }
}
