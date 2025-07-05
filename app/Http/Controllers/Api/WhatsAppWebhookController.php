<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * WhatsApp Webhook Controller
 * 
 * Gerencia webhooks do WhatsApp seguindo padrões SOLID:
 * - SRP: Responsabilidade única de processar webhooks
 * - DIP: Depende de abstrações (Service)
 */
class WhatsAppWebhookController extends Controller
{
    public function __construct(
        private WhatsAppService $whatsAppService
    ) {}

    /**
     * Verificação do webhook (GET)
     */
    public function verify(Request $request): JsonResponse
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        $expectedToken = config('services.whatsapp.webhook_verify_token');

        if ($mode === 'subscribe' && $token === $expectedToken) {
            Log::info('WhatsApp webhook verificado com sucesso');
            return response()->json((int) $challenge);
        }

        Log::warning('WhatsApp webhook: token inválido', [
            'provided_token' => $token,
            'mode' => $mode
        ]);

        return response()->json(['error' => 'Token inválido'], 403);
    }

    /**
     * Processamento de mensagens (POST)
     */
    public function handle(Request $request): JsonResponse
    {
        try {
            // Log do webhook recebido
            Log::channel('whatsapp')->info('Webhook recebido', [
                'headers' => $request->headers->all(),
                'body' => $request->all()
            ]);

            // Validação de assinatura (opcional, mas recomendado)
            if (!$this->validateSignature($request)) {
                Log::warning('WhatsApp webhook: assinatura inválida');
                return response()->json(['error' => 'Assinatura inválida'], 403);
            }

            // Processamento via service
            $result = $this->whatsAppService->processIncomingMessage($request->all());

            if ($result->success) {
                return response()->json(['status' => 'ok']);
            }

            Log::error('Erro ao processar webhook WhatsApp', [
                'error' => $result->error,
                'data' => $request->all()
            ]);

            return response()->json(['error' => $result->error], $result->statusCode);

        } catch (\Exception $e) {
            Log::error('Erro crítico no webhook WhatsApp', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);

            return response()->json(['error' => 'Erro interno'], 500);
        }
    }

    /**
     * Valida assinatura do webhook
     */
    private function validateSignature(Request $request): bool
    {
        $secret = config('services.whatsapp.webhook_secret');
        
        if (!$secret) {
            // Se não há secret configurado, pula a validação
            return true;
        }

        $signature = $request->header('X-Hub-Signature-256');
        
        if (!$signature) {
            return false;
        }

        $expectedSignature = 'sha256=' . hash_hmac('sha256', $request->getContent(), $secret);
        
        return hash_equals($expectedSignature, $signature);
    }
}
