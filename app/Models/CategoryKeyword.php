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
 *     schema="CategoryKeyword",
 *     type="object",
 *     title="Category Keyword",
 *     description="Keywords for automatic categorization",
 *     @OA\Property(property="id", type="integer", description="Keyword ID"),
 *     @OA\Property(property="category_id", type="integer", description="Category ID"),
 *     @OA\Property(property="keyword", type="string", description="Keyword text"),
 *     @OA\Property(property="weight", type="integer", description="Weight for classification"),
 *     @OA\Property(property="is_exact_match", type="boolean", description="Requires exact match"),
 *     @OA\Property(property="is_case_sensitive", type="boolean", description="Case sensitive matching"),
 *     @OA\Property(property="is_active", type="boolean", description="Is keyword active"),
 *     @OA\Property(property="language", type="string", description="Language code"),
 *     @OA\Property(property="match_count", type="integer", description="Number of matches"),
 *     @OA\Property(property="success_rate", type="number", format="float", description="Success rate percentage"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * @property int $id
 * @property int $category_id
 * @property string $keyword
 * @property int $weight Weight for classification algorithm
 * @property bool $is_exact_match
 * @property bool $is_case_sensitive
 * @property bool $is_active
 * @property string $language
 * @property int $match_count
 * @property numeric $success_rate
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Category $category
 * @property-read string $keyword_normalized
 * @property-read string $weight_label
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryKeyword active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryKeyword byCategory(int $categoryId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryKeyword byLanguage(string $language)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryKeyword exactMatch()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryKeyword highWeight(int $minWeight = 3)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryKeyword newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryKeyword newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryKeyword partialMatch()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryKeyword query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryKeyword whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryKeyword whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryKeyword whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryKeyword whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryKeyword whereIsCaseSensitive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryKeyword whereIsExactMatch($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryKeyword whereKeyword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryKeyword whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryKeyword whereMatchCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryKeyword whereSuccessRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryKeyword whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CategoryKeyword whereWeight($value)
 * @mixin \Eloquent
 */
class CategoryKeyword extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'keyword',
        'weight',
        'is_exact_match',
        'is_case_sensitive',
        'is_active',
        'language',
        'match_count',
        'success_rate',
    ];

    protected $casts = [
        'weight' => 'integer',
        'is_exact_match' => 'boolean',
        'is_case_sensitive' => 'boolean',
        'is_active' => 'boolean',
        'match_count' => 'integer',
        'success_rate' => 'decimal:2',
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

    public function getKeywordNormalizedAttribute(): string
    {
        $keyword = $this->keyword;

        if (!$this->is_case_sensitive) {
            $keyword = strtolower($keyword);
        }

        return trim($keyword);
    }

    public function getWeightLabelAttribute(): string
    {
        return match(true) {
            $this->weight >= 5 => 'Muito Alto',
            $this->weight >= 3 => 'Alto',
            $this->weight >= 2 => 'Médio',
            default => 'Baixo',
        };
    }

    // ========================================
    // Scopes
    // ========================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByLanguage($query, string $language)
    {
        return $query->where('language', $language);
    }

    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeHighWeight($query, int $minWeight = 3)
    {
        return $query->where('weight', '>=', $minWeight);
    }

    public function scopeExactMatch($query)
    {
        return $query->where('is_exact_match', true);
    }

    public function scopePartialMatch($query)
    {
        return $query->where('is_exact_match', false);
    }
}
