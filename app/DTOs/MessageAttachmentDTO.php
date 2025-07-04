<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\MessageAttachment;

readonly class MessageAttachmentDTO
{
    public function __construct(
        public int $id,
        public int $messageId,
        public string $fileName,
        public string $filePath,
        public ?string $originalName,
        public int $fileSize,
        public string $mimeType,
        public ?string $thumbnailPath,
        public ?int $duration,
        public ?array $dimensions,
        public bool $isScanned,
        public ?array $scanResult,
        public string $status,
        public \DateTime $createdAt,
        public \DateTime $updatedAt
    ) {}

    public static function fromModel(MessageAttachment $attachment): self
    {
        return new self(
            id: $attachment->id,
            messageId: $attachment->message_id,
            fileName: $attachment->file_name,
            filePath: $attachment->file_path,
            originalName: $attachment->original_name,
            fileSize: $attachment->file_size,
            mimeType: $attachment->mime_type,
            thumbnailPath: $attachment->thumbnail_path,
            duration: $attachment->duration,
            dimensions: $attachment->dimensions,
            isScanned: $attachment->is_scanned,
            scanResult: $attachment->scan_result,
            status: $attachment->status,
            createdAt: $attachment->created_at,
            updatedAt: $attachment->updated_at
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'message_id' => $this->messageId,
            'file_name' => $this->fileName,
            'file_path' => $this->filePath,
            'original_name' => $this->originalName,
            'file_size' => $this->fileSize,
            'mime_type' => $this->mimeType,
            'thumbnail_path' => $this->thumbnailPath,
            'duration' => $this->duration,
            'dimensions' => $this->dimensions,
            'is_scanned' => $this->isScanned,
            'scan_result' => $this->scanResult,
            'status' => $this->status,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
