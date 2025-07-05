<?php

namespace App\Services;

use App\DTOs\WhatsAppMessageDTO;
use App\DTOs\WhatsAppResponseDTO;
use App\Repositories\WhatsAppRepository;
use App\Models\Contact;
use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * WhatsApp Service
 * 
 * Implementa lógica de negócio para integração com WhatsApp.
 * Segue padrões SOLID/DDD:
 * - SRP: Responsabilidade única de orquestrar operações WhatsApp
 * - DIP: Depende de abstrações (Repository)
 * - OCP: Extensível para novos tipos de mensagem
 */
class WhatsAppService
{
    public function __construct(
        private WhatsAppRepository $whatsAppRepository,
        private ContactQueryService $contactQueryService
    ) {}

    /**
     * Envia mensagem de texto
     */
    public function sendTextMessage(string $phoneNumber, string $message, ?int $conversationId = null): WhatsAppResponseDTO
    {
        try {
            // Validação de entrada
            $this->validatePhoneNumber($phoneNumber);
            $this->validateMessageContent($message);

            // Criação do DTO
            $messageDto = WhatsAppMessageDTO::createTextMessage($phoneNumber, $message);

            // Envio via repository
            $response = $this->whatsAppRepository->sendTextMessage(
                $messageDto->to,
                $messageDto->content
            );

            // Processamento da resposta
            $result = $this->processApiResponse($response);

            // Registro no banco de dados se bem-sucedido
            if ($result->success) {
                $this->saveOutgoingMessage($messageDto, $result->messageId, $conversationId);
            }

            return $result;
        } catch (\InvalidArgumentException $e) {
            Log::warning('WhatsApp: Validação falhou', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage()
            ]);

            return WhatsAppResponseDTO::error($e->getMessage(), 'VALIDATION_ERROR', 422);
        } catch (RequestException $e) {
            Log::error('WhatsApp: Erro na API', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage(),
                'response' => $e->response?->json()
            ]);

            return WhatsAppResponseDTO::error(
                'Erro ao enviar mensagem WhatsApp',
                'API_ERROR',
                $e->response?->status() ?? 500
            );
        }
    }

    /**
     * Envia mensagem com template
     */
    public function sendTemplateMessage(
        string $phoneNumber,
        string $templateName,
        array $components = [],
        ?int $conversationId = null
    ): WhatsAppResponseDTO {
        try {
            $this->validatePhoneNumber($phoneNumber);
            $this->validateTemplateName($templateName);

            $messageDto = WhatsAppMessageDTO::createTemplateMessage($phoneNumber, $templateName, $components);

            $response = $this->whatsAppRepository->sendTemplateMessage(
                $messageDto->to,
                $messageDto->templateName,
                $messageDto->templateComponents ?? []
            );

            $result = $this->processApiResponse($response);

            if ($result->success) {
                $this->saveOutgoingMessage($messageDto, $result->messageId, $conversationId);
            }

            return $result;
        } catch (\InvalidArgumentException $e) {
            return WhatsAppResponseDTO::error($e->getMessage(), 'VALIDATION_ERROR', 422);
        } catch (RequestException $e) {
            return WhatsAppResponseDTO::error(
                'Erro ao enviar template WhatsApp',
                'API_ERROR',
                $e->response?->status() ?? 500
            );
        }
    }

    /**
     * Envia mensagem com mídia
     */
    public function sendMediaMessage(
        string $phoneNumber,
        string $type,
        string $mediaUrl,
        ?string $caption = null,
        ?int $conversationId = null
    ): WhatsAppResponseDTO {
        try {
            $this->validatePhoneNumber($phoneNumber);
            $this->validateMediaType($type);
            $this->validateMediaUrl($mediaUrl);

            $messageDto = WhatsAppMessageDTO::createMediaMessage($phoneNumber, $type, $mediaUrl, $caption);

            $response = $this->whatsAppRepository->sendMediaMessage(
                $messageDto->to,
                $messageDto->type,
                $messageDto->mediaUrl,
                $messageDto->caption
            );

            $result = $this->processApiResponse($response);

            if ($result->success) {
                $this->saveOutgoingMessage($messageDto, $result->messageId, $conversationId);
            }

            return $result;
        } catch (\InvalidArgumentException $e) {
            return WhatsAppResponseDTO::error($e->getMessage(), 'VALIDATION_ERROR', 422);
        } catch (RequestException $e) {
            return WhatsAppResponseDTO::error(
                'Erro ao enviar mídia WhatsApp',
                'API_ERROR',
                $e->response?->status() ?? 500
            );
        }
    }

    /**
     * Processa mensagem recebida via webhook
     */
    public function processIncomingMessage(array $webhookData): WhatsAppResponseDTO
    {
        try {
            // Validação básica do webhook
            if (!$this->isValidWebhookData($webhookData)) {
                return WhatsAppResponseDTO::error('Dados de webhook inválidos', 'INVALID_WEBHOOK', 400);
            }

            DB::beginTransaction();

            foreach ($webhookData['entry'] ?? [] as $entry) {
                foreach ($entry['changes'] ?? [] as $change) {
                    if ($change['field'] === 'messages') {
                        $this->processMessageChange($change['value']);
                    }
                }
            }

            DB::commit();

            return WhatsAppResponseDTO::success(['processed' => true]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('WhatsApp: Erro ao processar webhook', [
                'data' => $webhookData,
                'error' => $e->getMessage()
            ]);

            return WhatsAppResponseDTO::error('Erro interno', 'PROCESSING_ERROR', 500);
        }
    }

    /**
     * Marca mensagem como lida
     */
    public function markAsRead(string $messageId): WhatsAppResponseDTO
    {
        try {
            $response = $this->whatsAppRepository->markMessageAsRead($messageId);
            return $this->processApiResponse($response);
        } catch (RequestException $e) {
            return WhatsAppResponseDTO::error(
                'Erro ao marcar como lida',
                'API_ERROR',
                $e->response?->status() ?? 500
            );
        }
    }

    /**
     * Obtém templates disponíveis
     */
    public function getAvailableTemplates(): WhatsAppResponseDTO
    {
        try {
            $response = $this->whatsAppRepository->getTemplates();
            return $this->processApiResponse($response);
        } catch (RequestException $e) {
            return WhatsAppResponseDTO::error(
                'Erro ao obter templates',
                'API_ERROR',
                $e->response?->status() ?? 500
            );
        }
    }

    /**
     * Verifica status da configuração
     */
    public function checkConfiguration(): WhatsAppResponseDTO
    {
        $errors = $this->whatsAppRepository->validateConfiguration();

        if (!empty($errors)) {
            return WhatsAppResponseDTO::error(
                'Configuração incompleta: ' . implode(', ', $errors),
                'CONFIG_ERROR',
                500
            );
        }

        try {
            $response = $this->whatsAppRepository->getBusinessProfile();
            return $this->processApiResponse($response);
        } catch (RequestException $e) {
            return WhatsAppResponseDTO::error(
                'Erro ao verificar configuração',
                'CONFIG_TEST_FAILED',
                $e->response?->status() ?? 500
            );
        }
    }

    /**
     * Processa resposta da API
     */
    private function processApiResponse($response): WhatsAppResponseDTO
    {
        if ($response->successful()) {
            $data = $response->json();
            $messageId = $data['messages'][0]['id'] ?? null;

            return WhatsAppResponseDTO::success($data, $messageId);
        }

        $errorData = $response->json();
        $error = $errorData['error']['message'] ?? 'Erro desconhecido da API';
        $errorCode = $errorData['error']['code'] ?? null;

        return WhatsAppResponseDTO::error($error, $errorCode, $response->status());
    }

    /**
     * Salva mensagem enviada no banco
     */
    private function saveOutgoingMessage(WhatsAppMessageDTO $messageDto, ?string $messageId, ?int $conversationId): void
    {
        try {
            // Busca ou cria contato
            $contact = $this->contactQueryService->findByPhone($messageDto->to);
            if (!$contact) {
                $contact = Contact::create([
                    'name' => $messageDto->to,
                    'phone' => $messageDto->to,
                    'channel' => 'whatsapp'
                ]);
            }

            // Busca ou cria conversa
            $conversation = $conversationId ?
                Conversation::find($conversationId) :
                $this->findOrCreateConversation($contact->id);

            // Salva mensagem
            Message::create([
                'conversation_id' => $conversation->id,
                'contact_id' => $contact->id,
                'content' => $this->getMessageContentForStorage($messageDto),
                'type' => $messageDto->type,
                'direction' => 'outbound',
                'channel' => 'whatsapp',
                'external_id' => $messageId,
                'status' => 'sent',
                'metadata' => json_encode($messageDto->toArray())
            ]);
        } catch (\Exception $e) {
            Log::error('WhatsApp: Erro ao salvar mensagem', [
                'message_dto' => $messageDto->toArray(),
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Processa alteração de mensagem do webhook
     */
    private function processMessageChange(array $changeData): void
    {
        foreach ($changeData['messages'] ?? [] as $messageData) {
            $this->saveIncomingMessage($messageData, $changeData['metadata']);
        }

        foreach ($changeData['statuses'] ?? [] as $statusData) {
            $this->updateMessageStatus($statusData);
        }
    }

    /**
     * Salva mensagem recebida
     */
    private function saveIncomingMessage(array $messageData, array $metadata): void
    {
        $phoneNumber = $messageData['from'];

        // Busca ou cria contato
        $contact = $this->contactQueryService->findByPhone($phoneNumber);
        if (!$contact) {
            $contact = Contact::create([
                'name' => $phoneNumber,
                'phone' => $phoneNumber,
                'channel' => 'whatsapp'
            ]);
        }

        // Busca ou cria conversa
        $conversation = $this->findOrCreateConversation($contact->id);

        // Extrai conteúdo baseado no tipo
        $content = $this->extractMessageContent($messageData);
        $type = $messageData['type'];

        Message::create([
            'conversation_id' => $conversation->id,
            'contact_id' => $contact->id,
            'content' => $content,
            'type' => $type,
            'direction' => 'inbound',
            'channel' => 'whatsapp',
            'external_id' => $messageData['id'],
            'status' => 'received',
            'metadata' => json_encode($messageData)
        ]);

        // Marca como lida automaticamente
        $this->markAsRead($messageData['id']);
    }

    /**
     * Atualiza status da mensagem
     */
    private function updateMessageStatus(array $statusData): void
    {
        Message::where('external_id', $statusData['id'])
            ->update(['status' => $statusData['status']]);
    }

    /**
     * Busca ou cria conversa
     */
    private function findOrCreateConversation(int $contactId): Conversation
    {
        return Conversation::firstOrCreate(
            [
                'contact_id' => $contactId,
                'channel' => 'whatsapp',
                'status' => 'active'
            ],
            [
                'started_at' => now(),
                'last_message_at' => now()
            ]
        );
    }

    // Métodos de validação
    private function validatePhoneNumber(string $phoneNumber): void
    {
        if (empty($phoneNumber)) {
            throw new \InvalidArgumentException('Número de telefone é obrigatório');
        }
    }

    private function validateMessageContent(string $content): void
    {
        if (empty(trim($content))) {
            throw new \InvalidArgumentException('Conteúdo da mensagem é obrigatório');
        }
    }

    private function validateTemplateName(string $templateName): void
    {
        if (empty($templateName)) {
            throw new \InvalidArgumentException('Nome do template é obrigatório');
        }
    }

    private function validateMediaType(string $type): void
    {
        if (!in_array($type, ['image', 'video', 'audio', 'document'])) {
            throw new \InvalidArgumentException('Tipo de mídia inválido');
        }
    }

    private function validateMediaUrl(string $url): void
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('URL de mídia inválida');
        }
    }

    private function isValidWebhookData(array $data): bool
    {
        return isset($data['entry']) && is_array($data['entry']);
    }

    private function getMessageContentForStorage(WhatsAppMessageDTO $messageDto): string
    {
        return match ($messageDto->type) {
            'text' => $messageDto->content,
            'template' => "Template: {$messageDto->templateName}",
            'image', 'video', 'audio', 'document' => $messageDto->mediaUrl,
            default => $messageDto->content
        };
    }

    private function extractMessageContent(array $messageData): string
    {
        return match ($messageData['type']) {
            'text' => $messageData['text']['body'],
            'image' => $messageData['image']['caption'] ?? '[Imagem]',
            'video' => $messageData['video']['caption'] ?? '[Vídeo]',
            'audio' => '[Áudio]',
            'document' => $messageData['document']['filename'] ?? '[Documento]',
            default => '[Mensagem não suportada]'
        };
    }
}
