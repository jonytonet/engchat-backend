<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Schema(
 *     schema="MessageAttachment",
 *     type="object",
 *     title="Message Attachment",
 *     description="Attachment file associated with a message",
 *     @OA\Property(property="id", type="integer", description="Attachment ID"),
 *     @OA\Property(property="message_id", type="integer", description="Associated message ID"),
 *     @OA\Property(property="file_name", type="string", description="Stored file name"),
 *     @OA\Property(property="original_name", type="string", description="Original file name"),
 *     @OA\Property(property="file_path", type="string", description="File storage path"),
 *     @OA\Property(property="file_size", type="integer", description="File size in bytes"),
 *     @OA\Property(property="mime_type", type="string", description="File MIME type"),
 *     @OA\Property(property="thumbnail_path", type="string", description="Thumbnail path for media files"),
 *     @OA\Property(property="duration", type="integer", description="Duration in seconds for audio/video"),
 *     @OA\Property(property="dimensions", type="object", description="Width/height for images/videos"),
 *     @OA\Property(property="status", type="string", enum={"uploading", "processing", "ready", "failed"}),
 *     @OA\Property(property="is_scanned", type="boolean", description="Whether file has been scanned for security"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class MessageAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'file_name',
        'file_path',
        'original_name',
        'file_size',
        'mime_type',
        'thumbnail_path',
        'duration',
        'dimensions',
        'is_scanned',
        'scan_result',
        'status',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'duration' => 'integer',
        'dimensions' => 'array',
        'is_scanned' => 'boolean',
        'scan_result' => 'array',
    ];

    // ========================================
    // Relationships
    // ========================================

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    // ========================================
    // Accessors & Mutators
    // ========================================

    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function getIsVideoAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    public function getIsAudioAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'audio/');
    }

    public function getIsDocumentAttribute(): bool
    {
        return !$this->is_image && !$this->is_video && !$this->is_audio;
    }

    public function getUrlAttribute(): ?string
    {
        if (!$this->file_path) {
            return null;
        }

        return Storage::url($this->file_path);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->thumbnail_path) {
            return null;
        }

        return Storage::url($this->thumbnail_path);
    }

    // ========================================
    // Scopes
    // ========================================

    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('mime_type', 'like', $type . '/%');
    }

    public function scopeScanned($query, bool $scanned = true)
    {
        return $query->where('is_scanned', $scanned);
    }
}
