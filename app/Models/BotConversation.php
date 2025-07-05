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
 *     schema="BotConversation",
 *     type="object",
 *     title="Bot Conversation",
 *     description="Controle de conversas do bot para pré-atendimento",
 *     @OA\Property(property="id", type="integer", description="Bot conversation ID"),
 *     @OA\Property(property="conversation_id", type="integer", description="Conversation ID"),
 *     @OA\Property(property="contact_id", type="integer", description="Contact ID"),
 *     @OA\Property(property="current_step", type="string", description="Current bot step"),
 *     @OA\Property(property="bot_flow_id", type="integer", description="Bot flow ID"),
 *     @OA\Property(property="collected_data", type="object", description="Data collected during flow"),
 *     @OA\Property(property="classification_result", type="object", description="Classification result"),
 *     @OA\Property(property="confidence_score", type="number", format="float", description="Classification confidence"),
 *     @OA\Property(property="requires_human", type="boolean", description="Requires human handoff"),
 *     @OA\Property(property="handoff_reason", type="string", description="Reason for handoff"),
 *     @OA\Property(property="is_completed", type="boolean", description="Is bot flow completed"),
 *     @OA\Property(property="started_at", type="string", format="date-time"),
 *     @OA\Property(property="completed_at", type="string", format="date-time"),
 *     @OA\Property(property="escalated_at", type="string", format="date-time"),
 *     @OA\Property(property="last_interaction_at", type="string", format="date-time")
 * )
 * @property int $id
 * @property int $conversation_id
 * @property int $contact_id
 * @property string $current_step welcome, collect_name, classify, etc
 * @property int|null $bot_flow_id
 * @property array<array-key, mixed>|null $collected_data Dados coletados durante o fluxo
 * @property array<array-key, mixed>|null $classification_result Resultado da classificação automática
 * @property numeric|null $confidence_score Confiança da classificação
 * @property bool $requires_human
 * @property string|null $handoff_reason
 * @property array<array-key, mixed>|null $attempted_classifications Tentativas de classificação
 * @property bool $is_completed
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $escalated_at
 * @property \Illuminate\Support\Carbon $started_at
 * @property \Illuminate\Support\Carbon $last_interaction_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Contact $contact
 * @property-read \App\Models\Conversation $conversation
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BotConversation active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BotConversation byStep(string $step)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BotConversation completed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BotConversation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BotConversation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BotConversation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BotConversation requiringHuman()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BotConversation whereAttemptedClassifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BotConversation whereBotFlowId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BotConversation whereClassificationResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BotConversation whereCollectedData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BotConversation whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BotConversation whereConfidenceScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BotConversation whereContactId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BotConversation whereConversationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BotConversation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BotConversation whereCurrentStep($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BotConversation whereEscalatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BotConversation whereHandoffReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BotConversation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BotConversation whereIsCompleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BotConversation whereLastInteractionAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BotConversation whereRequiresHuman($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BotConversation whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BotConversation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class BotConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'contact_id',
        'current_step',
        'bot_flow_id',
        'collected_data',
        'classification_result',
        'confidence_score',
        'requires_human',
        'handoff_reason',
        'attempted_classifications',
        'is_completed',
        'completed_at',
        'escalated_at',
        'started_at',
        'last_interaction_at',
    ];

    protected $casts = [
        'collected_data' => 'array',
        'classification_result' => 'array',
        'attempted_classifications' => 'array',
        'confidence_score' => 'decimal:2',
        'requires_human' => 'boolean',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
        'escalated_at' => 'datetime',
        'started_at' => 'datetime',
        'last_interaction_at' => 'datetime',
    ];

    // ========================================
    // Relationships
    // ========================================

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    // ========================================
    // Scopes
    // ========================================

    public function scopeRequiringHuman($query)
    {
        return $query->where('requires_human', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_completed', false);
    }

    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    public function scopeByStep($query, string $step)
    {
        return $query->where('current_step', $step);
    }

    // ========================================
    // Helper Methods
    // ========================================

    public function markAsCompleted(): void
    {
        $this->update([
            'is_completed' => true,
            'completed_at' => now(),
        ]);
    }

    public function escalateToHuman(string $reason): void
    {
        $this->update([
            'requires_human' => true,
            'handoff_reason' => $reason,
            'escalated_at' => now(),
        ]);
    }

    public function updateStep(string $step): void
    {
        $this->update([
            'current_step' => $step,
            'last_interaction_at' => now(),
        ]);
    }

    public function addCollectedData(array $data): void
    {
        $currentData = $this->collected_data ?? [];
        $this->update([
            'collected_data' => array_merge($currentData, $data),
            'last_interaction_at' => now(),
        ]);
    }
}
