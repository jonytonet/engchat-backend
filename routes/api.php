<?php

use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\WhatsAppWebhookController;
use App\Http\Controllers\Api\WhatsAppMessageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Aqui estão as rotas da API para as aplicações Flutter
| Todas as rotas requerem autenticação via Sanctum
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Rotas protegidas da API
Route::middleware(['auth:sanctum'])->group(function () {

    // Conversas - API REST completa para Flutter
    Route::apiResource('conversations', ConversationController::class);

    // Rotas extras para conversas
    Route::prefix('conversations')->name('conversations.')->group(function () {
        Route::post('{conversation}/assign', [ConversationController::class, 'assign'])
            ->name('assign');

        Route::post('{conversation}/close', [ConversationController::class, 'close'])
            ->name('close');

        Route::post('{conversation}/reopen', [ConversationController::class, 'reopen'])
            ->name('reopen');

        Route::get('contact/{contact}', [ConversationController::class, 'byContact'])
            ->name('by-contact');

        Route::get('statistics', [ConversationController::class, 'statistics'])
            ->name('statistics');
    });

    // TODO: Adicionar outras rotas da API
    // Route::apiResource('messages', MessageController::class);
    // Route::apiResource('contacts', ContactController::class);
    // Route::apiResource('channels', ChannelController::class);

    // WhatsApp API - Envio de mensagens (protegido)
    Route::prefix('whatsapp')->name('whatsapp.')->group(function () {
        Route::post('send-text', [WhatsAppMessageController::class, 'sendText'])
            ->name('send-text');
        
        Route::post('send-template', [WhatsAppMessageController::class, 'sendTemplate'])
            ->name('send-template');
        
        Route::post('send-media', [WhatsAppMessageController::class, 'sendMedia'])
            ->name('send-media');
        
        Route::post('mark-read', [WhatsAppMessageController::class, 'markAsRead'])
            ->name('mark-read');
        
        Route::get('templates', [WhatsAppMessageController::class, 'getTemplates'])
            ->name('templates');
        
        Route::get('status', [WhatsAppMessageController::class, 'getStatus'])
            ->name('status');
    });
});

// Rotas públicas (webhook, etc)
Route::prefix('webhooks')->name('webhooks.')->group(function () {
    // WhatsApp Webhook - público (Facebook precisa acessar)
    Route::get('whatsapp', [WhatsAppWebhookController::class, 'verify'])
        ->name('whatsapp.verify');
    
    Route::post('whatsapp', [WhatsAppWebhookController::class, 'handle'])
        ->name('whatsapp.handle');
    
    // TODO: Implementar outros webhooks
    // Route::post('instagram', [InstagramWebhookController::class, 'handle']);
});
