<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * DTO para Bot Conversation
 * 
 * @property-read int $conversation_id
 * @property-read int $contact_id
 * @property-read string $current_step
 * @property-read int|null $bot_flow_id
 * @property-read array|null $collected_data
 * @property-read array|null $classification_result
 * @property-read float|null $confidence_score
 * @property-read bool $requires_human
 * @property-read string|null $handoff_reason
 * @property-read array|null $attempted_classifications
 * @property-read bool $is_completed
 */
readonly class BotConversationDTO
{
    public function __construct(
        public int $conversation_id,
        public int $contact_id,
        public string $current_step,
        public ?int $bot_flow_id = null,
        public ?array $collected_data = null,
        public ?array $classification_result = null,
        public ?float $confidence_score = null,
        public bool $requires_human = false,
        public ?string $handoff_reason = null,
        public ?array $attempted_classifications = null,
        public bool $is_completed = false,
    ) {}

    /**
     * Cria um DTO a partir de um array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            conversation_id: $data['conversation_id'],
            contact_id: $data['contact_id'],
            current_step: $data['current_step'],
            bot_flow_id: $data['bot_flow_id'] ?? null,
            collected_data: $data['collected_data'] ?? null,
            classification_result: $data['classification_result'] ?? null,
            confidence_score: $data['confidence_score'] ?? null,
            requires_human: $data['requires_human'] ?? false,
            handoff_reason: $data['handoff_reason'] ?? null,
            attempted_classifications: $data['attempted_classifications'] ?? null,
            is_completed: $data['is_completed'] ?? false,
        );
    }

    /**
     * Converte o DTO para array
     */
    public function toArray(): array
    {
        return [
            'conversation_id' => $this->conversation_id,
            'contact_id' => $this->contact_id,
            'current_step' => $this->current_step,
            'bot_flow_id' => $this->bot_flow_id,
            'collected_data' => $this->collected_data,
            'classification_result' => $this->classification_result,
            'confidence_score' => $this->confidence_score,
            'requires_human' => $this->requires_human,
            'handoff_reason' => $this->handoff_reason,
            'attempted_classifications' => $this->attempted_classifications,
            'is_completed' => $this->is_completed,
        ];
    }

    /**
     * Verifica se o bot requer intervenção humana
     */
    public function requiresHumanIntervention(): bool
    {
        return $this->requires_human ||
            $this->current_step === 'escalated' ||
            ($this->confidence_score && $this->confidence_score < 0.7);
    }

    /**
     * Obtém o próximo passo do bot
     */
    public function getNextStep(): string
    {
        return match ($this->current_step) {
            'welcome' => 'collect_name',
            'collect_name' => 'collect_issue',
            'collect_issue' => 'classify',
            'classify' => $this->requires_human ? 'escalate' : 'complete',
            'escalate' => 'handoff',
            default => 'complete'
        };
    }

    /**
     * Adiciona dados coletados
     */
    public function withCollectedData(array $newData): self
    {
        $currentData = $this->collected_data ?? [];
        $mergedData = array_merge($currentData, $newData);

        return new self(
            conversation_id: $this->conversation_id,
            contact_id: $this->contact_id,
            current_step: $this->current_step,
            bot_flow_id: $this->bot_flow_id,
            collected_data: $mergedData,
            classification_result: $this->classification_result,
            confidence_score: $this->confidence_score,
            requires_human: $this->requires_human,
            handoff_reason: $this->handoff_reason,
            attempted_classifications: $this->attempted_classifications,
            is_completed: $this->is_completed,
        );
    }

    /**
     * Atualiza o passo atual
     */
    public function withCurrentStep(string $step): self
    {
        return new self(
            conversation_id: $this->conversation_id,
            contact_id: $this->contact_id,
            current_step: $step,
            bot_flow_id: $this->bot_flow_id,
            collected_data: $this->collected_data,
            classification_result: $this->classification_result,
            confidence_score: $this->confidence_score,
            requires_human: $this->requires_human,
            handoff_reason: $this->handoff_reason,
            attempted_classifications: $this->attempted_classifications,
            is_completed: $this->is_completed,
        );
    }

    /**
     * Marca como requerendo intervenção humana
     */
    public function requireHuman(string $reason): self
    {
        return new self(
            conversation_id: $this->conversation_id,
            contact_id: $this->contact_id,
            current_step: $this->current_step,
            bot_flow_id: $this->bot_flow_id,
            collected_data: $this->collected_data,
            classification_result: $this->classification_result,
            confidence_score: $this->confidence_score,
            requires_human: true,
            handoff_reason: $reason,
            attempted_classifications: $this->attempted_classifications,
            is_completed: $this->is_completed,
        );
    }
}
