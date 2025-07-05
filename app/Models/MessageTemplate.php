<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 
 *
 * @OA\Schema (
 *     schema="MessageTemplate",
 *     type="object",
 *     title="Message Template",
 *     description="Templates de mensagens para respostas automáticas",
 *     @OA\Property(property="id", type="integer", description="Template ID"),
 *     @OA\Property(property="name", type="string", description="Template name"),
 *     @OA\Property(property="display_name", type="string", description="Display name"),
 *     @OA\Property(property="template_code", type="string", description="Unique template code"),
 *     @OA\Property(property="language", type="string", description="Language code"),
 *     @OA\Property(property="category", type="string", enum={"utility", "marketing", "authentication"}),
 *     @OA\Property(property="approval_status", type="string", enum={"pending", "approved", "rejected"}),
 *     @OA\Property(property="body_content", type="string", description="Template body content"),
 *     @OA\Property(property="header_content", type="string", description="Template header content"),
 *     @OA\Property(property="footer_content", type="string", description="Template footer content"),
 *     @OA\Property(property="parameters", type="array", @OA\Items(type="object")),
 *     @OA\Property(property="variables_count", type="integer", description="Number of variables"),
 *     @OA\Property(property="is_global", type="boolean", description="Is global template"),
 *     @OA\Property(property="is_active", type="boolean", description="Is template active")
 * )
 * @property int $id
 * @property string $name
 * @property string $display_name
 * @property string $template_code
 * @property string $language
 * @property string $category
 * @property string $approval_status
 * @property string $body_content
 * @property string|null $header_content
 * @property string|null $footer_content
 * @property array<array-key, mixed>|null $parameters Parâmetros dinâmicos do template
 * @property int $variables_count
 * @property bool $is_global
 * @property bool $is_active
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AutoResponse> $autoResponses
 * @property-read int|null $auto_responses_count
 * @property-read \App\Models\User $createdBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\QueueNotification> $queueNotifications
 * @property-read int|null $queue_notifications_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageTemplate active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageTemplate approved()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageTemplate byCategory(string $category)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageTemplate byCode(string $code)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageTemplate byLanguage(string $language)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageTemplate global()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageTemplate whereApprovalStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageTemplate whereBodyContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageTemplate whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageTemplate whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageTemplate whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageTemplate whereFooterContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageTemplate whereHeaderContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageTemplate whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageTemplate whereIsGlobal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageTemplate whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageTemplate whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageTemplate whereParameters($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageTemplate whereTemplateCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageTemplate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MessageTemplate whereVariablesCount($value)
 * @mixin \Eloquent
 */
class MessageTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'template_code',
        'language',
        'category',
        'approval_status',
        'body_content',
        'header_content',
        'footer_content',
        'parameters',
        'variables_count',
        'is_global',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'parameters' => 'array',
        'variables_count' => 'integer',
        'is_global' => 'boolean',
        'is_active' => 'boolean',
    ];

    // ========================================
    // Relationships
    // ========================================

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function queueNotifications(): HasMany
    {
        return $this->hasMany(QueueNotification::class);
    }

    public function autoResponses(): HasMany
    {
        return $this->hasMany(AutoResponse::class);
    }

    // ========================================
    // Scopes
    // ========================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    public function scopeByLanguage($query, string $language)
    {
        return $query->where('language', $language);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeGlobal($query)
    {
        return $query->where('is_global', true);
    }

    public function scopeByCode($query, string $code)
    {
        return $query->where('template_code', $code);
    }

    // ========================================
    // Helper Methods
    // ========================================

    public function processTemplate(array $variables = []): string
    {
        $content = $this->body_content;

        // Process numbered placeholders like {{1}}, {{2}}, etc.
        $content = preg_replace_callback('/\{\{(\d+)\}\}/', function ($matches) use ($variables) {
            $index = (int)$matches[1] - 1; // Convert to 0-based index
            return $variables[$index] ?? $matches[0];
        }, $content);

        // Process named placeholders like {{contact_name}}, {{queue_position}}, etc.
        $content = preg_replace_callback('/\{\{([a-zA-Z_]+)\}\}/', function ($matches) use ($variables) {
            $key = $matches[1];
            return $variables[$key] ?? $matches[0];
        }, $content);

        return $content;
    }

    public function getFullContent(array $variables = []): string
    {
        $content = '';

        if ($this->header_content) {
            $content .= $this->processTemplate($variables) . "\n\n";
        }

        $content .= $this->processTemplate($variables);

        if ($this->footer_content) {
            $content .= "\n\n" . $this->processTemplate($variables);
        }

        return trim($content);
    }

    public function approve(): void
    {
        $this->update(['approval_status' => 'approved']);
    }

    public function reject(): void
    {
        $this->update(['approval_status' => 'rejected']);
    }

    public function activate(): void
    {
        $this->update(['is_active' => true]);
    }

    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    public function extractVariables(): array
    {
        $content = $this->body_content . ' ' . ($this->header_content ?? '') . ' ' . ($this->footer_content ?? '');

        // Find all {{variable}} patterns
        preg_match_all('/\{\{([^}]+)\}\}/', $content, $matches);

        return array_unique($matches[1]);
    }

    public function updateVariablesCount(): void
    {
        $variables = $this->extractVariables();
        $this->update(['variables_count' => count($variables)]);
    }

    public function isReadyForUse(): bool
    {
        return $this->is_active &&
            $this->approval_status === 'approved' &&
            !empty($this->body_content);
    }
}
