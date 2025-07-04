<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ConversationStatus;
use App\Enums\Priority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // TODO: Implementar autorização específica
    }

    public function rules(): array
    {
        return [
            'status' => [
                'sometimes',
                'string',
                Rule::in(ConversationStatus::getValues())
            ],
            'priority' => [
                'sometimes',
                'string',
                Rule::in(Priority::getValues())
            ],
            'subject' => [
                'sometimes',
                'string',
                'max:255'
            ],
            'category_id' => [
                'sometimes',
                'nullable',
                'exists:categories,id'
            ],
            'assigned_to' => [
                'sometimes',
                'nullable',
                'exists:users,id'
            ],
            'metadata' => [
                'sometimes',
                'array'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'O status deve ser um dos valores válidos.',
            'priority.in' => 'A prioridade deve ser um dos valores válidos.',
            'subject.max' => 'O assunto não pode ter mais de 255 caracteres.',
            'category_id.exists' => 'A categoria selecionada não existe.',
            'assigned_to.exists' => 'O agente selecionado não existe.',
        ];
    }
}
