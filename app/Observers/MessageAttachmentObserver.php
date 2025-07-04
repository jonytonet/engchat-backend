<?php

namespace App\Observers;

use App\Models\MessageAttachment;
use App\Services\MessageAttachmentService;

class MessageAttachmentObserver
{
    public function __construct(
        private MessageAttachmentService $attachmentService
    ) {}

    /**
     * Handle the MessageAttachment "deleting" event.
     */
    public function deleting(MessageAttachment $attachment): void
    {
        // Delete physical files when model is being deleted
        $this->attachmentService->deleteAttachmentFiles($attachment);
    }

    /**
     * Handle the MessageAttachment "created" event.
     */
    public function created(MessageAttachment $attachment): void
    {
        // Auto-scan new attachments for security
        if (!$attachment->is_scanned) {
            $this->attachmentService->scanForSecurity($attachment);
        }
    }
}
