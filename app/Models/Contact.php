<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Contact",
 *     title="Contact",
 *     description="Model representing a customer contact",
 *     required={"name"},
 *     @OA\Property(property="id", type="integer", format="int64", description="Contact ID", example=1),
 *     @OA\Property(property="name", type="string", description="Contact full name", example="Maria Silva"),
 *     @OA\Property(property="email", type="string", format="email", nullable=true, description="Contact email", example="maria@example.com"),
 *     @OA\Property(property="phone", type="string", nullable=true, description="Contact phone number", example="+5511999999999"),
 *     @OA\Property(property="display_name", type="string", nullable=true, description="Display name for chat", example="Maria"),
 *     @OA\Property(property="company", type="string", nullable=true, description="Company name", example="Empresa XYZ"),
 *     @OA\Property(property="document", type="string", nullable=true, description="Document number (CPF, CNPJ)", example="123.456.789-10"),
 *     @OA\Property(property="tags", type="array", @OA\Items(type="string"), nullable=true, description="Contact tags", example={"vip", "premium"}),
 *     @OA\Property(property="priority", type="string", enum={"low", "medium", "high", "urgent"}, description="Contact priority", example="medium"),
 *     @OA\Property(property="blacklisted", type="boolean", description="Whether contact is blacklisted", example=false),
 *     @OA\Property(property="blacklist_reason", type="string", nullable=true, description="Reason for blacklisting"),
 *     @OA\Property(property="preferred_language", type="string", description="Preferred language", example="pt-BR"),
 *     @OA\Property(property="timezone", type="string", description="Contact timezone", example="America/Sao_Paulo"),
 *     @OA\Property(property="last_interaction", type="string", format="date-time", nullable=true, description="Last interaction timestamp"),
 *     @OA\Property(property="total_interactions", type="integer", description="Total number of interactions", example=15),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp")
 * )
 */

class Contact extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'display_name',
        'company',
        'document',
        'tags',
        'priority',
        'blacklisted',
        'blacklist_reason',
        'preferred_language',
        'timezone',
        'last_interaction',
        'total_interactions',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tags' => 'array',
        'blacklisted' => 'boolean',
        'last_interaction' => 'datetime',
    ];

    // ===== RELATIONSHIPS =====

    /**
     * Get conversations for this contact.
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    /**
     * Get messages from this contact.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id')
            ->where('sender_type', 'contact');
    }


    /**
     * Get the latest conversation for this contact.
     */
    public function latestConversation()
    {
        return $this->hasOne(Conversation::class)->latest();
    }

    /**
     * Get active conversations for this contact.
     */
    public function activeConversations(): HasMany
    {
        return $this->hasMany(Conversation::class)
            ->whereIn('status', ['open', 'pending']);
    }

    // ===== SCOPES =====

    /**
     * Scope to get non-blacklisted contacts.
     */
    public function scopeNotBlacklisted($query)
    {
        return $query->where('blacklisted', false);
    }

    /**
     * Scope to get blacklisted contacts.
     */
    public function scopeBlacklisted($query)
    {
        return $query->where('blacklisted', true);
    }

    /**
     * Scope to get contacts by priority.
     */
    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope to get VIP contacts.
     */
    public function scopeVip($query)
    {
        return $query->where('priority', 'urgent')
            ->orWhereJsonContains('tags', 'vip');
    }

    /**
     * Scope to search contacts.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('display_name', 'like', "%{$search}%")
              ->orWhere('company', 'like', "%{$search}%")
              ->orWhere('document', 'like', "%{$search}%");
        });
    }

    // ===== METHODS =====

    /**
     * Check if contact is blacklisted.
     */
    public function isBlacklisted(): bool
    {
        return $this->blacklisted;
    }


    /**
     * Get display name (preferred name for UI).
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->display_name ?? $this->name ?? $this->phone ?? 'Unknown';
    }

    /**
     * Update last interaction timestamp.
     */
    public function updateLastInteraction(): void
    {
        $this->update([
            'last_interaction' => now(),
            'total_interactions' => $this->total_interactions + 1,
        ]);
    }

    /**
     * Add tag to contact.
     */
    public function addTag(string $tag): void
    {
        $tags = $this->tags ?? [];

        if (!in_array($tag, $tags)) {
            $tags[] = $tag;
            $this->tags = $tags;
            $this->save();
        }
    }

    /**
     * Remove tag from contact.
     */
    public function removeTag(string $tag): void
    {
        if (!$this->tags) {
            return;
        }

        $tags = array_filter($this->tags, function ($t) use ($tag) {
            return $t !== $tag;
        });

        $this->tags = array_values($tags);
        $this->save();
    }

    /**
     * Get contact statistics.
     */
    public function getStats(): array
    {
        return [
            'total_conversations' => $this->conversations()->count(),
            'open_conversations' => $this->conversations()->where('status', 'open')->count(),
            'pending_conversations' => $this->conversations()->where('status', 'pending')->count(),
            'closed_conversations' => $this->conversations()->where('status', 'closed')->count(),
            'total_messages' => $this->messages()->count(),
            'avg_satisfaction' => $this->conversations()
                ->whereNotNull('satisfaction_rating')
                ->avg('satisfaction_rating'),
            'last_interaction' => $this->last_interaction,
            'total_interactions' => $this->total_interactions,
            'is_vip' => $this->isVip(),
            'is_blacklisted' => $this->isBlacklisted(),
        ];
    }

    /**
     * Get preferred contact method.
     */
    public function getPreferredContactMethod(): string
    {
        if ($this->phone) {
            return 'whatsapp';
        }

        if ($this->email) {
            return 'email';
        }

        return 'web';
    }
}
