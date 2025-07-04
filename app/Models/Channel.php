<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
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
 */

class Channel extends Model
{
    use HasFactory, SoftDeletes;

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

    /**
     * Get channel integrations.
     */
    public function integrations(): HasMany
    {
        return $this->hasMany(ChannelIntegration::class);
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

    // ===== METHODS =====

    /**
     * Check if channel is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if channel is WhatsApp.
     */
    public function isWhatsApp(): bool
    {
        return $this->type === 'whatsapp';
    }

    /**
     * Check if channel is Telegram.
     */
    public function isTelegram(): bool
    {
        return $this->type === 'telegram';
    }

    /**
     * Check if channel is Web.
     */
    public function isWeb(): bool
    {
        return $this->type === 'web';
    }

    /**
     * Check if auto-response is enabled.
     */
    public function hasAutoResponse(): bool
    {
        return $this->auto_response_enabled;
    }

    /**
     * Check if channel is within working hours.
     */
    public function isWithinWorkingHours(): bool
    {
        if (!$this->working_hours) {
            return true; // No restrictions means always available
        }

        $now = now();
        $dayOfWeek = strtolower($now->format('l'));

        if (!isset($this->working_hours[$dayOfWeek])) {
            return false;
        }

        $hours = $this->working_hours[$dayOfWeek];
        $startTime = $now->copy()->setTimeFromTimeString($hours['start']);
        $endTime = $now->copy()->setTimeFromTimeString($hours['end']);

        return $now->between($startTime, $endTime);
    }

    /**
     * Get channel configuration value.
     */
    public function getConfig(string $key, $default = null)
    {
        return data_get($this->configuration, $key, $default);
    }

    /**
     * Set channel configuration value.
     */
    public function setConfig(string $key, $value): void
    {
        $config = $this->configuration ?? [];
        data_set($config, $key, $value);
        $this->configuration = $config;
        $this->save();
    }

    /**
     * Get channel statistics.
     */
    public function getStats(): array
    {
        return [
            'total_conversations' => $this->conversations()->count(),
            'open_conversations' => $this->conversations()->where('status', 'open')->count(),
            'pending_conversations' => $this->conversations()->where('status', 'pending')->count(),
            'closed_conversations' => $this->conversations()->where('status', 'closed')->count(),
            'is_active' => $this->is_active,
            'auto_response_enabled' => $this->auto_response_enabled,
            'is_within_working_hours' => $this->isWithinWorkingHours(),
        ];
    }

    /**
     * Get the primary integration for this channel.
     */
    public function getPrimaryIntegration(): ?ChannelIntegration
    {
        return $this->integrations()
            ->where('is_active', true)
            ->first();
    }

    /**
     * Send message through this channel.
     * This method should be implemented by channel-specific services.
     */
    public function sendMessage(string $to, string $message, array $options = []): bool
    {
        // This is a placeholder - actual implementation should be done
        // by channel-specific services (WhatsAppService, TelegramService, etc.)
        return false;
    }
}
