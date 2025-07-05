<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 
 *
 * @OA\Schema (
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
 * @property int $id
 * @property string $name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $avatar
 * @property string $display_name
 * @property string|null $company
 * @property string|null $document
 * @property array<array-key, mixed>|null $tags
 * @property string|null $metadata
 * @property string|null $blocked_at
 * @property string|null $last_interaction_at
 * @property string $priority
 * @property bool $blacklisted
 * @property string|null $blacklist_reason
 * @property string $preferred_language
 * @property string $timezone
 * @property \Illuminate\Support\Carbon|null $last_interaction
 * @property int $total_interactions
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Conversation> $activeConversations
 * @property-read int|null $active_conversations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Conversation> $conversations
 * @property-read int|null $conversations_count
 * @property-read \App\Models\Conversation|null $latestConversation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Message> $messages
 * @property-read int|null $messages_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact blacklisted()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact byPriority(string $priority)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact notBlacklisted()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact search(string $search)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact vip()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereBlacklistReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereBlacklisted($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereBlockedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereCompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereDocument($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereLastInteraction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereLastInteractionAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact wherePreferredLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereTotalInteractions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Contact withoutTrashed()
 * @mixin \Eloquent
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
        'businesspartner_id',
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
     * Check if contact is VIP.
     */
    public function isVip(): bool
    {
        return $this->priority === 'urgent' ||
            (is_array($this->tags) && in_array('vip', $this->tags));
    }

    /**
     * Get display name (preferred name for UI).
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->display_name ?? $this->name ?? $this->phone ?? 'Unknown';
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

    // ===== ERP INTEGRATION ATTRIBUTES =====

    /**
     * Check if contact has business partner integration.
     */
    public function hasBusinessPartnerIntegration(): bool
    {
        return !empty($this->businesspartner_id);
    }

    /**
     * Get the business partner identifier.
     */
    public function getBusinessPartnerId(): ?string
    {
        return $this->businesspartner_id;
    }
}
