<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
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
