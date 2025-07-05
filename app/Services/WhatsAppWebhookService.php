<?php

namespace App\Services;

use App\DTOs\ContactDTO;
use App\DTOs\CreateContactDTO;
use App\DTOs\CreateConversationDTO;
use App\DTOs\MessageDTO;
use App\DTOs\SendMessageDTO;
use App\Enums\MessageType;
use App\Jobs\ProcessIncomingWhatsAppMessage;
use App\Jobs\SendAutoResponse;
use App\Models\Channel;
use App\Models\Contact;
use App\Models\Conversation;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class WhatsAppWebhookService extends BaseService
{
    public function __construct(
        private ContactService $contactService,
        private ConversationService $conversationService,
        private MessageService $messageService,
        private BotClassificationService $botClassificationService,
        private AutoResponseService $autoResponseService
    ) {
        parent::__construct();
    }

    /**
     * Verify WhatsApp webhook token
     */
    public function verifyToken(string $token): bool
    {
        $expectedToken = config('services.whatsapp.webhook_verify_token');

        if (empty($expectedToken)) {
            Log::error('WhatsApp webhook verify token not configured');
            return false;
        }

        return hash_equals($expectedToken, $token);
    }

    /**
     * Validate webhook signature
     */
    public function validateSignature(Request $request): bool
    {
        $signature = $request->header('X-Hub-Signature-256');
        $payload = $request->getContent();
        $secret = config('services.whatsapp.webhook_secret');

        if (empty($signature) || empty($secret)) {
            Log::warning('WhatsApp webhook signature or secret missing');
            return false;
        }

        $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Process incoming webhook payload
     */
    public function processWebhook(array $payload): void
    {
        try {
            if (!isset($payload['entry']) || !is_array($payload['entry'])) {
                Log::warning('Invalid WhatsApp webhook payload structure', ['payload' => $payload]);
                return;
            }

            foreach ($payload['entry'] as $entry) {
                $this->processEntry($entry);
            }
        } catch (\Exception $e) {
            Log::error('Error processing WhatsApp webhook', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);
            throw $e;
        }
    }

    /**
     * Process individual webhook entry
     */
    private function processEntry(array $entry): void
    {
        if (!isset($entry['changes']) || !is_array($entry['changes'])) {
            return;
        }

        foreach ($entry['changes'] as $change) {
            if ($change['field'] === 'messages') {
                $this->processMessagesChange($change['value']);
            } elseif ($change['field'] === 'message_status') {
                $this->processMessageStatus($change['value']);
            }
        }
    }

    /**
     * Process messages webhook change
     */
    private function processMessagesChange(array $value): void
    {
        if (isset($value['messages']) && is_array($value['messages'])) {
            foreach ($value['messages'] as $message) {
                ProcessIncomingWhatsAppMessage::dispatch($message, $value);
            }
        }
    }

    /**
     * Process message status updates
     */
    private function processMessageStatus(array $value): void
    {
        if (isset($value['statuses']) && is_array($value['statuses'])) {
            foreach ($value['statuses'] as $status) {
                $this->updateMessageStatus($status);
            }
        }
    }

    /**
     * Update message delivery status
     */
    private function updateMessageStatus(array $status): void
    {
        try {
            $messageId = $status['id'] ?? null;
            $statusType = $status['status'] ?? null;

            if (!$messageId || !$statusType) {
                return;
            }

            // Find message by WhatsApp message ID
            $message = $this->messageService->findByWhatsAppId($messageId);

            if (!$message) {
                Log::warning('Message not found for status update', [
                    'whatsapp_message_id' => $messageId,
                    'status' => $statusType
                ]);
                return;
            }

            // Update message status
            $this->messageService->updateDeliveryStatus($message->id, $statusType);

            Log::info('Message status updated', [
                'message_id' => $message->id,
                'whatsapp_message_id' => $messageId,
                'status' => $statusType
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating message status', [
                'error' => $e->getMessage(),
                'status' => $status
            ]);
        }
    }

    /**
     * Process incoming WhatsApp message
     */
    public function processIncomingMessage(array $message, array $context): void
    {
        try {
            // Extract message data
            $from = $message['from'] ?? null;
            $timestamp = $message['timestamp'] ?? null;
            $messageType = $message['type'] ?? 'text';
            $messageContent = $this->extractMessageContent($message);

            if (!$from || !$messageContent) {
                Log::warning('Invalid WhatsApp message format', ['message' => $message]);
                return;
            }

            // Get or create contact
            $contact = $this->getOrCreateContact($from, $context);

            // Get or create conversation
            $conversation = $this->getOrCreateConversation($contact);

            // Create message
            $messageDto = new SendMessageDTO(
                conversationId: $conversation->id,
                contactId: $contact->id,
                content: $messageContent['text'] ?? '',
                type: $this->mapWhatsAppMessageType($messageType),
                metadata: [
                    'whatsapp_message_id' => $message['id'] ?? null,
                    'whatsapp_timestamp' => $timestamp,
                    'whatsapp_type' => $messageType,
                    'whatsapp_context' => $message['context'] ?? null,
                    'original_message' => $messageContent
                ]
            );

            $createdMessage = $this->messageService->create($messageDto);

            // Process attachments if any
            if (isset($messageContent['media'])) {
                $this->processMessageMedia($createdMessage, $messageContent['media']);
            }

            // Check for auto-response
            $this->checkAutoResponse($conversation, $createdMessage, $messageContent['text'] ?? '');

            Log::info('WhatsApp message processed successfully', [
                'message_id' => $createdMessage->id,
                'contact_id' => $contact->id,
                'conversation_id' => $conversation->id,
                'whatsapp_from' => $from
            ]);
        } catch (\Exception $e) {
            Log::error('Error processing incoming WhatsApp message', [
                'error' => $e->getMessage(),
                'message' => $message,
                'context' => $context
            ]);
            throw $e;
        }
    }

    /**
     * Extract content from WhatsApp message
     */
    private function extractMessageContent(array $message): array
    {
        $type = $message['type'] ?? 'text';
        $content = ['text' => '', 'media' => null];

        switch ($type) {
            case 'text':
                $content['text'] = $message['text']['body'] ?? '';
                break;

            case 'image':
            case 'video':
            case 'audio':
            case 'document':
                $media = $message[$type] ?? [];
                $content['text'] = $media['caption'] ?? '';
                $content['media'] = [
                    'type' => $type,
                    'id' => $media['id'] ?? null,
                    'mime_type' => $media['mime_type'] ?? null,
                    'filename' => $media['filename'] ?? null,
                    'sha256' => $media['sha256'] ?? null
                ];
                break;

            case 'location':
                $location = $message['location'] ?? [];
                $content['text'] = sprintf(
                    'Localização: %s, %s',
                    $location['latitude'] ?? 'N/A',
                    $location['longitude'] ?? 'N/A'
                );
                break;

            case 'contact':
                $contacts = $message['contacts'] ?? [];
                $contactInfo = $contacts[0] ?? [];
                $content['text'] = sprintf(
                    'Contato: %s',
                    $contactInfo['formatted_name'] ?? 'N/A'
                );
                break;
        }

        return $content;
    }

    /**
     * Get or create contact from WhatsApp number
     */
    private function getOrCreateContact(string $whatsappNumber, array $context): Contact
    {
        // Try to find existing contact by WhatsApp number
        $contact = Contact::where('whatsapp_number', $whatsappNumber)->first();

        if ($contact) {
            return $contact;
        }

        // Extract profile information from context
        $profile = $context['contacts'][0] ?? [];
        $profileName = $profile['profile']['name'] ?? null;

        // Create new contact
        $createContactDto = new CreateContactDTO(
            name: $profileName ?: "WhatsApp +$whatsappNumber",
            whatsappNumber: $whatsappNumber,
            email: null,
            phone: $whatsappNumber,
            metadata: [
                'whatsapp_profile' => $profile,
                'source' => 'whatsapp_webhook'
            ]
        );

        return $this->contactService->create($createContactDto);
    }

    /**
     * Get or create conversation for contact
     */
    private function getOrCreateConversation(Contact $contact): Conversation
    {
        // Find active WhatsApp conversation for this contact
        $conversation = Conversation::where('contact_id', $contact->id)
            ->where('channel_id', $this->getWhatsAppChannel()->id)
            ->whereIn('status', ['open', 'assigned'])
            ->first();

        if ($conversation) {
            return $conversation;
        }

        // Create new conversation
        $createConversationDto = new CreateConversationDTO(
            contactId: $contact->id,
            channelId: $this->getWhatsAppChannel()->id,
            subject: 'WhatsApp Chat',
            metadata: [
                'source' => 'whatsapp_webhook',
                'auto_created' => true
            ]
        );

        return $this->conversationService->create($createConversationDto);
    }

    /**
     * Get WhatsApp channel
     */
    private function getWhatsAppChannel(): Channel
    {
        return Channel::where('name', 'whatsapp')->firstOrFail();
    }

    /**
     * Map WhatsApp message type to internal enum
     */
    private function mapWhatsAppMessageType(string $whatsappType): MessageType
    {
        return match ($whatsappType) {
            'image', 'video', 'audio', 'document' => MessageType::FILE,
            'location' => MessageType::LOCATION,
            'contact' => MessageType::CONTACT,
            default => MessageType::TEXT
        };
    }

    /**
     * Process message media attachments
     */
    private function processMessageMedia($message, array $media): void
    {
        // This will be handled by MessageAttachmentService
        // For now, just log the media info
        Log::info('WhatsApp media received', [
            'message_id' => $message->id,
            'media' => $media
        ]);
    }

    /**
     * Check and trigger auto-response if applicable
     */
    private function checkAutoResponse(Conversation $conversation, $message, string $content): void
    {
        try {
            // Use BotClassificationService to identify keywords
            $keywords = $this->botClassificationService->extractKeywords($content);

            if (empty($keywords)) {
                return;
            }

            // Check for auto-response
            $autoResponse = $this->autoResponseService->findByKeywords($keywords);

            if ($autoResponse) {
                SendAutoResponse::dispatch($conversation->id, $autoResponse->id)
                    ->delay(now()->addSeconds(2)); // Small delay for natural feel
            }
        } catch (\Exception $e) {
            Log::error('Error checking auto-response', [
                'error' => $e->getMessage(),
                'conversation_id' => $conversation->id,
                'message_id' => $message->id
            ]);
        }
    }
}
