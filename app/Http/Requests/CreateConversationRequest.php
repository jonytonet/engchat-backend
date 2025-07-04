<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Priority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Implementar autorização específica se necessário
    }

    public function rules(): array
    {
        return [
            'contact_id' => [
                'required',
                'integer',
                Rule::exists('contacts', 'id')->whereNull('deleted_at'),
            ],
            'channel_id' => [
                'required',
                'integer',
                Rule::exists('channels', 'id')->where('is_active', true),
            ],
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where('is_active', true),
            ],
            'priority' => [
                'sometimes',
                'string',
                Rule::in(Priority::getValues()),
            ],
            'subject' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'contact_id.required' => 'O contato é obrigatório.',
            'contact_id.exists' => 'O contato informado não existe.',
            'channel_id.required' => 'O canal é obrigatório.',
            'channel_id.exists' => 'O canal informado não existe ou não está ativo.',
            'category_id.exists' => 'A categoria informada não existe ou não está ativa.',
            'priority.in' => 'A prioridade deve ser: ' . implode(', ', Priority::getValues()),
            'subject.max' => 'O assunto não pode ter mais que 255 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'contact_id' => 'contato',
            'channel_id' => 'canal',
            'category_id' => 'categoria',
            'priority' => 'prioridade',
            'subject' => 'assunto',
        ];
    }
}
