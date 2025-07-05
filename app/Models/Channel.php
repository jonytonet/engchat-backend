<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 
 *
 * @OA\Schema (
 *     schema="Channel",
 *     title="Channel",
 *     description="Model representing a communication channel (WhatsApp, Telegram, etc.)",
 *     required={"name", "type"},
 *     @OA\Property(property="id", type="integer", format="int64", description="Channel ID", example=1),
 *     @OA\Property(property="name", type="string", description="Channel name", example="WhatsApp Business"),
 *     @OA\Property(property="type", type="string", enum={"whatsapp", "telegram", "web", "facebook", "instagram"}, description="Channel type", example="whatsapp"),
 *     @OA\Property(property="configuration", type="object", nullable=true, description="Channel configuration", example={"phone": "554133808848", "api_version": "v17.0"}),
 *     @OA\Property(property="is_active", type="boolean", description="Whether channel is active", example=true),
 *     @OA\Property(property="priority", type="integer", description="Channel priority", example=1),
 *     @OA\Property(property="working_hours", type="object", nullable=true, description="Working hours configuration"),
 *     @OA\Property(property="auto_response_enabled", type="boolean", description="Whether auto-response is enabled", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp")
 * )
 * @property int $id
 * @property string $name
 * @property string $type
 * @property array<array-key, mixed>|null $configuration
 * @property bool $is_active
 * @property int $priority
 * @property array<array-key, mixed>|null $working_hours
 * @property bool $auto_response_enabled
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Conversation> $conversations
 * @property-read int|null $conversations_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Channel active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Channel byPriority()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Channel byType(string $type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Channel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Channel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Channel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Channel whereAutoResponseEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Channel whereConfiguration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Channel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Channel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Channel whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Channel whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Channel wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Channel whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Channel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Channel whereWorkingHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Channel withAutoResponse()
 * @mixin \Eloquent
 */

class Channel extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'type',
        'configuration',
        'is_active',
        'priority',
        'working_hours',
        'auto_response_enabled',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'configuration' => 'array',
        'working_hours' => 'array',
        'is_active' => 'boolean',
        'auto_response_enabled' => 'boolean',
    ];

    // ===== RELATIONSHIPS =====

    /**
     * Get conversations for this channel.
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }



    // ===== SCOPES =====

    /**
     * Scope to get only active channels.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get channels by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get channels with auto-response enabled.
     */
    public function scopeWithAutoResponse($query)
    {
        return $query->where('auto_response_enabled', true);
    }

    /**
     * Scope to order by priority.
     */
    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'desc');
    }

}
