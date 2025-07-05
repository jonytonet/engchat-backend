<?php

namespace App\Repositories;

use Illuminate\Http\Client\Response;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * WhatsApp API Repository
 * 
 * Responsável apenas pelo acesso direto à API do WhatsApp.
 * Implementa padrões SOLID: SRP - única responsabilidade de acesso à API.
 * Não contém lógica de negócio, apenas formatação de payloads e chamadas HTTP.
 */
class WhatsAppRepository
{
    private string $apiUrl;
    private string $accessToken;
    private string $phoneNumberId;
    private string $apiVersion;

    public function __construct()
    {
        $this->apiUrl = config('services.whatsapp.api_url', env('WHATSAPP_API_URL'));
        $this->accessToken = config('services.whatsapp.access_token', env('WHATSAPP_ACCESS_TOKEN'));
        $this->phoneNumberId = config('services.whatsapp.phone_number_id', env('WHATSAPP_PHONE_NUMBER_ID'));
        $this->apiVersion = config('services.whatsapp.api_version', env('WHATSAPP_API_VERSION', 'v20.0'));
    }

    /**
     * Envia mensagem de texto via WhatsApp API
     */
    public function sendTextMessage(string $to, string $message): Response
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->formatPhoneNumber($to),
            'type' => 'text',
            'text' => [
                'body' => $message
            ]
        ];

        return $this->makeApiCall("{$this->phoneNumberId}/messages", $payload);
    }

    /**
     * Envia mensagem com template via WhatsApp API
     */
    public function sendTemplateMessage(string $to, string $templateName, array $components = []): Response
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->formatPhoneNumber($to),
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => [
                    'code' => 'pt_BR'
                ]
            ]
        ];

        if (!empty($components)) {
            $payload['template']['components'] = $components;
        }

        return $this->makeApiCall("{$this->phoneNumberId}/messages", $payload);
    }

    /**
     * Envia mensagem com mídia via WhatsApp API
     */
    public function sendMediaMessage(string $to, string $type, string $mediaUrl, ?string $caption = null): Response
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->formatPhoneNumber($to),
            'type' => $type,
            $type => [
                'link' => $mediaUrl
            ]
        ];

        if ($caption && in_array($type, ['image', 'video', 'document'])) {
            $payload[$type]['caption'] = $caption;
        }

        return $this->makeApiCall("{$this->phoneNumberId}/messages", $payload);
    }

    /**
     * Marca mensagem como lida
     */
    public function markMessageAsRead(string $messageId): Response
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'status' => 'read',
            'message_id' => $messageId
        ];

        return $this->makeApiCall("{$this->phoneNumberId}/messages", $payload);
    }

    /**
     * Obtém templates disponíveis
     */
    public function getTemplates(): Response
    {
        $businessAccountId = config('services.whatsapp.business_account_id', env('WHATSAPP_BUSINESS_ACCOUNT_ID'));
        $endpoint = "{$businessAccountId}/message_templates";

        return $this->makeApiCall($endpoint, [], 'GET');
    }

    /**
     * Obtém informações do perfil business
     */
    public function getBusinessProfile(): Response
    {
        $endpoint = $this->phoneNumberId;
        $params = ['fields' => 'display_phone_number,verified_name,quality_rating'];

        return $this->makeApiCall($endpoint, $params, 'GET');
    }

    /**
     * Upload de mídia para WhatsApp
     */
    public function uploadMedia(string $filePath, string $type): Response
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'type' => $type
        ];

        return Http::withToken($this->accessToken)
            ->withoutVerifying()
            ->attach('file', file_get_contents($filePath), basename($filePath))
            ->post("{$this->apiUrl}/{$this->apiVersion}/{$this->phoneNumberId}/media", $payload);
    }

    /**
     * Obtém URL de mídia por ID
     */
    public function getMediaUrl(string $mediaId): Response
    {
        return $this->makeApiCall($mediaId, [], 'GET');
    }

    /**
     * Formata número de telefone para padrão internacional
     */
    private function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove caracteres não numéricos
        $phone = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Se não tem código do país, adiciona Brasil (55)
        if (strlen($phone) === 11 && substr($phone, 0, 1) !== '55') {
            $phone = '55' . $phone;
        }

        return $phone;
    }

    /**
     * Executa chamada para API do WhatsApp
     */
    private function makeApiCall(string $endpoint, array $data = [], string $method = 'POST'): Response
    {
        $url = "{$this->apiUrl}/{$this->apiVersion}/{$endpoint}";

        try {
            $request = Http::withToken($this->accessToken)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])
                ->withoutVerifying(); // Disable SSL verification for development

            if ($method === 'GET') {
                $response = $request->get($url, $data);
            } else {
                $response = $request->post($url, $data);
            }

            // Log da requisição para debug
            Log::channel('whatsapp')->info('WhatsApp API Request', [
                'method' => $method,
                'url' => $url,
                'data' => $data,
                'response_status' => $response->status(),
                'response_body' => $response->json()
            ]);

            return $response;
        } catch (RequestException $e) {
            Log::channel('whatsapp')->error('WhatsApp API Error', [
                'method' => $method,
                'url' => $url,
                'data' => $data,
                'error' => $e->getMessage(),
                'response' => $e->response?->json()
            ]);

            throw $e;
        }
    }

    /**
     * Valida se as configurações estão presentes
     */
    public function validateConfiguration(): array
    {
        $errors = [];

        if (empty($this->apiUrl)) {
            $errors[] = 'WHATSAPP_API_URL não configurada';
        }

        if (empty($this->accessToken)) {
            $errors[] = 'WHATSAPP_ACCESS_TOKEN não configurado';
        }

        if (empty($this->phoneNumberId)) {
            $errors[] = 'WHATSAPP_PHONE_NUMBER_ID não configurado';
        }

        return $errors;
    }
}
