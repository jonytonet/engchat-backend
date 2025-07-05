<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * 
 *
 * @OA\Schema (
 *     schema="AutoResponse",
 *     type="object",
 *     title="Auto Response",
 *     description="Automated response configuration",
 *     @OA\Property(property="id", type="integer", description="Auto response ID"),
 *     @OA\Property(property="category_id", type="integer", description="Category ID"),
 *     @OA\Property(property="name", type="string", description="Response name"),
 *     @OA\Property(property="trigger_type", type="string", enum={"keyword", "welcome", "offline", "category"}),
 *     @OA\Property(property="trigger_keyword", type="string", description="Trigger keyword"),
 *     @OA\Property(property="trigger_conditions", type="object", description="Additional trigger conditions"),
 *     @OA\Property(property="response_text", type="string", description="Response message"),
 *     @OA\Property(property="response_type", type="string", enum={"text", "template", "redirect", "transfer"}),
 *     @OA\Property(property="response_data", type="object", description="Additional response data"),
 *     @OA\Property(property="delay_seconds", type="integer", description="Delay before sending"),
 *     @OA\Property(property="working_hours", type="object", description="Working hours restriction"),
 *     @OA\Property(property="conditions", type="object", description="Additional conditions"),
 *     @OA\Property(property="is_active", type="boolean", description="Is response active"),
 *     @OA\Property(property="usage_count", type="integer", description="Number of times used"),
 *     @OA\Property(property="success_rate", type="number", format="float", description="Success rate percentage"),
 *     @OA\Property(property="priority", type="integer", description="Priority level"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * @property int $id
 * @property int|null $category_id
 * @property string $name
 * @property string $trigger_type
 * @property string|null $trigger_keyword
 * @property array<array-key, mixed>|null $trigger_conditions
 * @property string $response_text
 * @property string $response_type
 * @property array<array-key, mixed>|null $response_data Additional data for template/redirect
 * @property int $delay_seconds
 * @property array<array-key, mixed>|null $working_hours
 * @property array<array-key, mixed>|null $conditions Additional conditions for triggering
 * @property bool $is_active
 * @property int $usage_count
 * @property numeric $success_rate
 * @property int $priority
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Category|null $category
 * @property-read string $priority_label
 * @property-read string $response_type_label
 * @property-read string $trigger_type_label
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutoResponse active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutoResponse byCategory(int $categoryId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutoResponse byKeyword(string $keyword)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutoResponse byTriggerType(string $type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutoResponse highPriority(int $minPriority = 3)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutoResponse newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutoResponse newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutoResponse orderByPriority()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutoResponse query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutoResponse whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutoResponse whereConditions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutoResponse whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutoResponse whereDelaySeconds($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutoResponse whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutoResponse whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutoResponse whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutoResponse wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutoResponse whereResponseData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutoResponse whereResponseText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutoResponse whereResponseType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutoResponse whereSuccessRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutoResponse whereTriggerConditions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutoResponse whereTriggerKeyword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutoResponse whereTriggerType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutoResponse whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutoResponse whereUsageCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AutoResponse whereWorkingHours($value)
 * @mixin \Eloquent
 */
class AutoResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'trigger_type',
        'trigger_keyword',
        'trigger_conditions',
        'response_text',
        'response_type',
        'response_data',
        'delay_seconds',
        'working_hours',
        'conditions',
        'is_active',
        'usage_count',
        'success_rate',
        'priority',
    ];

    protected $casts = [
        'trigger_conditions' => 'array',
        'response_data' => 'array',
        'working_hours' => 'array',
        'conditions' => 'array',
        'delay_seconds' => 'integer',
        'is_active' => 'boolean',
        'usage_count' => 'integer',
        'success_rate' => 'decimal:2',
        'priority' => 'integer',
    ];

    // ========================================
    // Relationships
    // ========================================

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // ========================================
    // Accessors & Mutators
    // ========================================

    public function getTriggerTypeLabelAttribute(): string
    {
        return match($this->trigger_type) {
            'keyword' => 'Palavra-chave',
            'welcome' => 'Boas-vindas',
            'offline' => 'Fora do horário',
            'category' => 'Categoria',
        };
    }

    public function getResponseTypeLabelAttribute(): string
    {
        return match($this->response_type) {
            'text' => 'Texto simples',
            'template' => 'Template',
            'redirect' => 'Redirecionamento',
            'transfer' => 'Transferência',
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return match(true) {
            $this->priority >= 5 => 'Muito Alta',
            $this->priority >= 3 => 'Alta',
            $this->priority >= 2 => 'Média',
            default => 'Baixa',
        };
    }

    // ========================================
    // Scopes
    // ========================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByTriggerType($query, string $type)
    {
        return $query->where('trigger_type', $type);
    }

    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByKeyword($query, string $keyword)
    {
        return $query->where('trigger_keyword', 'like', '%' . $keyword . '%');
    }

    public function scopeHighPriority($query, int $minPriority = 3)
    {
        return $query->where('priority', '>=', $minPriority);
    }

    public function scopeOrderByPriority($query)
    {
        return $query->orderBy('priority', 'desc');
    }
}
