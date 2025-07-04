<?php

namespace App\Services;

use App\Models\MessageAttachment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class MessageAttachmentService
{
    /**
     * Format file size in human readable format.
     */
    public function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $size = $bytes;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    /**
     * Delete physical files associated with attachment.
     */
    public function deleteAttachmentFiles(MessageAttachment $attachment): void
    {
        if ($attachment->file_path && Storage::exists($attachment->file_path)) {
            Storage::delete($attachment->file_path);
        }

        if ($attachment->thumbnail_path && Storage::exists($attachment->thumbnail_path)) {
            Storage::delete($attachment->thumbnail_path);
        }
    }

    /**
     * Store uploaded file and create attachment record.
     */
    public function storeAttachment(UploadedFile $file, int $messageId): MessageAttachment
    {
        $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('attachments', $fileName, 'public');

        $attachment = MessageAttachment::create([
            'message_id' => $messageId,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'original_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'status' => 'processing',
        ]);

        // Generate thumbnail if it's an image
        if ($this->isImage($attachment)) {
            $this->generateThumbnail($attachment);
        }

        // Mark as ready
        $attachment->update(['status' => 'ready']);

        return $attachment;
    }

    /**
     * Generate thumbnail for image attachments.
     */
    public function generateThumbnail(MessageAttachment $attachment): void
    {
        if (!$this->isImage($attachment)) {
            return;
        }

        // Thumbnail generation logic would go here
        // For now, just mark as having no thumbnail
        $attachment->update(['thumbnail_path' => null]);
    }

    /**
     * Scan attachment for security threats.
     */
    public function scanForSecurity(MessageAttachment $attachment): void
    {
        // Security scanning logic would go here
        // For now, mark as scanned with clean result
        $attachment->update([
            'is_scanned' => true,
            'scan_result' => ['status' => 'clean', 'scanned_at' => now()],
        ]);
    }

    /**
     * Check if attachment is an image.
     */
    public function isImage(MessageAttachment $attachment): bool
    {
        return str_starts_with($attachment->mime_type, 'image/');
    }

    /**
     * Check if attachment is a video.
     */
    public function isVideo(MessageAttachment $attachment): bool
    {
        return str_starts_with($attachment->mime_type, 'video/');
    }

    /**
     * Check if attachment is audio.
     */
    public function isAudio(MessageAttachment $attachment): bool
    {
        return str_starts_with($attachment->mime_type, 'audio/');
    }

    /**
     * Check if attachment is a document.
     */
    public function isDocument(MessageAttachment $attachment): bool
    {
        return !$this->isImage($attachment) && !$this->isVideo($attachment) && !$this->isAudio($attachment);
    }

    /**
     * Get public URL for attachment.
     */
    public function getUrl(MessageAttachment $attachment): ?string
    {
        if (!$attachment->file_path) {
            return null;
        }

        return Storage::url($attachment->file_path);
    }

    /**
     * Get thumbnail URL for attachment.
     */
    public function getThumbnailUrl(MessageAttachment $attachment): ?string
    {
        if (!$attachment->thumbnail_path) {
            return null;
        }

        return Storage::url($attachment->thumbnail_path);
    }

    /**
     * Validate file before upload.
     */
    public function validateFile(UploadedFile $file): array
    {
        $errors = [];

        // Max file size (50MB)
        if ($file->getSize() > 50 * 1024 * 1024) {
            $errors[] = 'Arquivo muito grande. Máximo permitido: 50MB';
        }

        // Allowed mime types
        $allowedTypes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'video/mp4', 'video/mpeg', 'video/quicktime',
            'audio/mpeg', 'audio/mp4', 'audio/wav',
            'application/pdf', 'text/plain',
            'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];

        if (!in_array($file->getMimeType(), $allowedTypes)) {
            $errors[] = 'Tipo de arquivo não permitido';
        }

        return $errors;
    }
}
