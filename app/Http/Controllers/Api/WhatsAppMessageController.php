<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * WhatsApp Messages Controller
 * 
 * API para envio de mensagens WhatsApp.
 * Segue padrões SOLID: SRP, DIP, validação de entrada.
 */
class WhatsAppMessageController extends Controller
{
    public function __construct(
        private WhatsAppService $whatsAppService
    ) {}

    /**
     * Envia mensagem de texto
     */
    public function sendText(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'phone' => 'required|string',
                'message' => 'required|string|max:4096',
                'conversation_id' => 'nullable|integer|exists:conversations,id'
            ]);

            $result = $this->whatsAppService->sendTextMessage(
                $validated['phone'],
                $validated['message'],
                $validated['conversation_id'] ?? null
            );

            return response()->json($result->toArray(), $result->statusCode);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Envia mensagem com template
     */
    public function sendTemplate(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'phone' => 'required|string',
                'template_name' => 'required|string',
                'components' => 'nullable|array',
                'conversation_id' => 'nullable|integer|exists:conversations,id'
            ]);

            $result = $this->whatsAppService->sendTemplateMessage(
                $validated['phone'],
                $validated['template_name'],
                $validated['components'] ?? [],
                $validated['conversation_id'] ?? null
            );

            return response()->json($result->toArray(), $result->statusCode);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Envia mensagem com mídia
     */
    public function sendMedia(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'phone' => 'required|string',
                'type' => 'required|string|in:image,video,audio,document',
                'media_url' => 'required|url',
                'caption' => 'nullable|string|max:1024',
                'conversation_id' => 'nullable|integer|exists:conversations,id'
            ]);

            $result = $this->whatsAppService->sendMediaMessage(
                $validated['phone'],
                $validated['type'],
                $validated['media_url'],
                $validated['caption'] ?? null,
                $validated['conversation_id'] ?? null
            );

            return response()->json($result->toArray(), $result->statusCode);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Lista templates disponíveis
     */
    public function getTemplates(): JsonResponse
    {
        $result = $this->whatsAppService->getAvailableTemplates();

        return response()->json($result->toArray(), $result->statusCode);
    }

    /**
     * Verifica status da configuração
     */
    public function getStatus(): JsonResponse
    {
        $result = $this->whatsAppService->checkConfiguration();

        return response()->json($result->toArray(), $result->statusCode);
    }

    /**
     * Marca mensagem como lida
     */
    public function markAsRead(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'message_id' => 'required|string'
            ]);

            $result = $this->whatsAppService->markAsRead($validated['message_id']);

            return response()->json($result->toArray(), $result->statusCode);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        }
    }
}
