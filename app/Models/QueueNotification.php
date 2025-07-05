<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @OA\Schema (
 *     schema="QueueNotification",
 *     type="object",
 *     title="Queue Notification",
 *     description="Notificações automáticas da fila",
 *     @OA\Property(property="id", type="integer", description="Notification ID"),
 *     @OA\Property(property="conversation_id", type="integer", description="Conversation ID"),
 *     @OA\Property(property="queue_id", type="integer", description="Queue ID"),
 *     @OA\Property(property="notification_type", type="string", enum={"position_update", "wait_time_update", "agent_assigned", "queue_timeout"}),
 *     @OA\Property(property="message_template_id", type="integer", description="Message template ID"),
 *     @OA\Property(property="custom_message", type="string", description="Custom message content"),
 *     @OA\Property(property="variables", type="object", description="Template variables"),
 *     @OA\Property(property="queue_position_at_time", type="integer", description="Queue position when notification was created"),
 *     @OA\Property(property="estimated_wait_time", type="integer", description="Estimated wait time in minutes"),
 *     @OA\Property(property="total_queue_size", type="integer", description="Total queue size"),
 *     @OA\Property(property="status", type="string", enum={"pending", "sent", "failed"}),
 *     @OA\Property(property="scheduled_at", type="string", format="date-time"),
 *     @OA\Property(property="sent_at", type="string", format="date-time"),
 *     @OA\Property(property="error_message", type="string", description="Error message if failed")
 * )
 * @property int $id
 * @property int $conversation_id
 * @property int $queue_id
 * @property string $notification_type
 * @property int|null $message_template_id
 * @property string|null $custom_message
 * @property array<array-key, mixed>|null $variables Variáveis para o template
 * @property int $queue_position_at_time
 * @property int|null $estimated_wait_time
 * @property int $total_queue_size
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $sent_at
 * @property string|null $error_message
 * @property \Illuminate\Support\Carbon $scheduled_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Conversation $conversation
 * @property-read \App\Models\MessageTemplate|null $messageTemplate
 * @property-read \App\Models\ConversationQueue $queue
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueNotification byType(string $type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueNotification failed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueNotification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueNotification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueNotification pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueNotification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueNotification scheduledFor($datetime)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueNotification sent()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueNotification whereConversationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueNotification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueNotification whereCustomMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueNotification whereErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueNotification whereEstimatedWaitTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueNotification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueNotification whereMessageTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueNotification whereNotificationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueNotification whereQueueId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueNotification whereQueuePositionAtTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueNotification whereScheduledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueNotification whereSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueNotification whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueNotification whereTotalQueueSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueNotification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueNotification whereVariables($value)
 * @mixin \Eloquent
 */
class QueueNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'queue_id',
        'notification_type',
        'message_template_id',
        'custom_message',
        'variables',
        'queue_position_at_time',
        'estimated_wait_time',
        'total_queue_size',
        'status',
        'sent_at',
        'error_message',
        'scheduled_at',
    ];

    protected $casts = [
        'variables' => 'array',
        'queue_position_at_time' => 'integer',
        'estimated_wait_time' => 'integer',
        'total_queue_size' => 'integer',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    // ========================================
    // Relationships
    // ========================================

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function queue(): BelongsTo
    {
        return $this->belongsTo(ConversationQueue::class, 'queue_id');
    }

    public function messageTemplate(): BelongsTo
    {
        return $this->belongsTo(MessageTemplate::class);
    }

    // ========================================
    // Scopes
    // ========================================

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeScheduledFor($query, $datetime)
    {
        return $query->where('scheduled_at', '<=', $datetime);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('notification_type', $type);
    }

    // ========================================
    // Helper Methods
    // ========================================

    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }

    public function getProcessedMessage(): string
    {
        if ($this->custom_message) {
            return $this->processVariables($this->custom_message);
        }

        if ($this->messageTemplate) {
            return $this->processVariables($this->messageTemplate->body_content);
        }

        return $this->getDefaultMessage();
    }

    private function processVariables(string $template): string
    {
        $variables = $this->variables ?? [];

        // Process numbered placeholders like {{1}}, {{2}}, etc.
        $template = preg_replace_callback('/\{\{(\d+)\}\}/', function ($matches) use ($variables) {
            $index = (int)$matches[1] - 1; // Convert to 0-based index
            return $variables[$index] ?? $matches[0];
        }, $template);

        // Process named placeholders like {{contact_name}}, {{queue_position}}, etc.
        $template = preg_replace_callback('/\{\{([a-zA-Z_]+)\}\}/', function ($matches) use ($variables) {
            $key = $matches[1];
            return $variables[$key] ?? $matches[0];
        }, $template);

        return $template;
    }

    private function getDefaultMessage(): string
    {
        return match ($this->notification_type) {
            'position_update' => "Você está na posição {$this->queue_position_at_time} da fila. Tempo estimado: {$this->estimated_wait_time} minutos.",
            'wait_time_update' => "Atualização: Tempo estimado de espera é {$this->estimated_wait_time} minutos.",
            'agent_assigned' => "Um agente foi designado para atendê-lo!",
            'queue_timeout' => "Tempo limite da fila atingido. Por favor, tente novamente mais tarde.",
            default => "Notificação da fila de atendimento."
        };
    }

    public function shouldBeSent(): bool
    {
        return $this->status === 'pending' &&
            $this->scheduled_at <= now();
    }
}
