<?php

namespace App\Providers;

use App\Repositories\WhatsAppRepository;
use App\Services\WhatsAppService;
use App\Services\ContactQueryService;
use Illuminate\Support\ServiceProvider;

/**
 * WhatsApp Service Provider
 * 
 * Registra serviços relacionados ao WhatsApp no container.
 * Segue padrões SOLID: DIP (Dependency Inversion Principle).
 */
class WhatsAppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Repository binding
        $this->app->singleton(WhatsAppRepository::class, function ($app) {
            return new WhatsAppRepository();
        });

        // Service binding with dependencies
        $this->app->singleton(WhatsAppService::class, function ($app) {
            return new WhatsAppService(
                $app->make(WhatsAppRepository::class),
                $app->make(ContactQueryService::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Nada específico para bootstrap no momento
    }
}
