<?php

namespace App\Services;

use App\Models\ConversationTransfer;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ConversationTransferService
{
    /**
     * Accept a conversation transfer.
     */
    public function accept(ConversationTransfer $transfer): bool
    {
        return DB::transaction(function () use ($transfer) {
            $transfer->status = 'accepted';
            $transfer->accepted_at = now();
            $saved = $transfer->save();

            if ($saved) {
                // Update conversation assignment
                $transfer->conversation->update([
                    'assigned_to' => $transfer->to_user_id,
                ]);

                // TODO: Dispatch event when ConversationTransferred event is created
                // event(new ConversationTransferred($transfer));
            }

            return $saved;
        });
    }

    /**
     * Reject a conversation transfer.
     */
    public function reject(ConversationTransfer $transfer, ?string $reason = null): bool
    {
        $transfer->status = 'rejected';

        if ($reason) {
            $transfer->notes = $transfer->notes . "\nRejected: " . $reason;
        }

        return $transfer->save();
    }

    /**
     * Create a new conversation transfer.
     */
    public function createTransfer(
        Conversation $conversation,
        User $fromUser,
        User $toUser,
        string $reason,
        ?string $notes = null
    ): ConversationTransfer {
        return ConversationTransfer::create([
            'conversation_id' => $conversation->id,
            'from_user_id' => $fromUser->id,
            'to_user_id' => $toUser->id,
            'reason' => $reason,
            'notes' => $notes,
            'status' => 'pending',
            'transferred_at' => now(),
        ]);
    }

    /**
     * Transfer conversation automatically to available agent.
     */
    public function autoTransfer(Conversation $conversation, string $reason): ?ConversationTransfer
    {
        $availableAgent = $this->findAvailableAgent($conversation);

        if (!$availableAgent) {
            return null;
        }

        return $this->createTransfer(
            $conversation,
            $conversation->assignedAgent()->first(),
            $availableAgent,
            $reason,
            'Auto-transferred by system'
        );
    }

    /**
     * Find an available agent for transfer.
     */
    protected function findAvailableAgent(Conversation $conversation): ?User
    {
        $category = $conversation->category;

        // First, try to find specialist in the category
        if ($category) {
            $specialist = User::active()
                ->online()
                ->whereHas('categories', function ($query) use ($category) {
                    $query->where('category_id', $category->id)
                          ->where('is_specialist', true);
                })
                ->whereHas('role', function ($query) {
                    $query->where('name', 'agent');
                })
                ->first();

            if ($specialist) {
                return $specialist;
            }
        }

        // If no specialist, find any available agent
        return User::active()
            ->online()
            ->whereHas('role', function ($query) {
                $query->where('name', 'agent');
            })
            ->first();
    }

    /**
     * Get transfer statistics for a user.
     */
    public function getUserTransferStats(User $user, int $days = 30): array
    {
        $startDate = now()->subDays($days);

        $transfersGiven = ConversationTransfer::where('from_user_id', $user->id)
            ->where('transferred_at', '>=', $startDate)
            ->count();

        $transfersReceived = ConversationTransfer::where('to_user_id', $user->id)
            ->where('transferred_at', '>=', $startDate)
            ->count();

        $transfersAccepted = ConversationTransfer::where('to_user_id', $user->id)
            ->where('status', 'accepted')
            ->where('transferred_at', '>=', $startDate)
            ->count();

        $transfersRejected = ConversationTransfer::where('to_user_id', $user->id)
            ->where('status', 'rejected')
            ->where('transferred_at', '>=', $startDate)
            ->count();

        return [
            'transfers_given' => $transfersGiven,
            'transfers_received' => $transfersReceived,
            'transfers_accepted' => $transfersAccepted,
            'transfers_rejected' => $transfersRejected,
            'acceptance_rate' => $transfersReceived > 0 ? ($transfersAccepted / $transfersReceived) * 100 : 0,
        ];
    }

    /**
     * Cancel a pending transfer.
     */
    public function cancelTransfer(ConversationTransfer $transfer): bool
    {
        if ($transfer->status !== 'pending') {
            return false;
        }

        return $transfer->delete();
    }

    /**
     * Get reason label for display.
     */
    public function getReasonLabel(string $reason): string
    {
        return match($reason) {
            'workload' => 'Sobrecarga de trabalho',
            'expertise' => 'Especialização necessária',
            'unavailable' => 'Indisponível',
            'other' => 'Outro motivo',
            default => $reason,
        };
    }
}
