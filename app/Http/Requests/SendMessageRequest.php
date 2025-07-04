<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\MessageType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Implementar autorização específica se necessário
    }

    public function rules(): array
    {
        $rules = [
            'type' => [
                'sometimes',
                'string',
                Rule::in(MessageType::getValues()),
            ],
            'content' => [
                'required_if:type,text,system',
                'string',
                'max:4000',
            ],
            'contact_id' => [
                'nullable',
                'integer',
                Rule::exists('contacts', 'id')->whereNull('deleted_at'),
            ],
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id'),
            ],
            'metadata' => [
                'sometimes',
                'array',
            ],
            'external_id' => [
                'nullable',
                'string',
                'max:255',
            ],
            'is_from_contact' => [
                'sometimes',
                'boolean',
            ],
            'reply_to_message_id' => [
                'nullable',
                'integer',
                Rule::exists('messages', 'id'),
            ],
        ];

        // Regras específicas para mensagens com mídia
        if (in_array($this->input('type'), ['image', 'video', 'audio', 'file'])) {
            $rules['media_url'] = ['required', 'string', 'url'];
            $rules['media_type'] = ['required', 'string', 'max:100'];
            $rules['media_size'] = ['nullable', 'integer', 'min:1'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'type.in' => 'O tipo de mensagem deve ser: ' . implode(', ', MessageType::getValues()),
            'content.required_if' => 'O conteúdo é obrigatório para mensagens de texto e sistema.',
            'content.max' => 'O conteúdo não pode ter mais que 4000 caracteres.',
            'contact_id.exists' => 'O contato informado não existe.',
            'user_id.exists' => 'O usuário informado não existe.',
            'media_url.required' => 'A URL da mídia é obrigatória para mensagens com arquivo.',
            'media_url.url' => 'A URL da mídia deve ser um endereço válido.',
            'media_type.required' => 'O tipo da mídia é obrigatório para mensagens com arquivo.',
            'media_size.min' => 'O tamanho da mídia deve ser maior que zero.',
            'reply_to_message_id.exists' => 'A mensagem de resposta informada não existe.',
        ];
    }

    public function attributes(): array
    {
        return [
            'type' => 'tipo',
            'content' => 'conteúdo',
            'contact_id' => 'contato',
            'user_id' => 'usuário',
            'media_url' => 'URL da mídia',
            'media_type' => 'tipo da mídia',
            'media_size' => 'tamanho da mídia',
            'external_id' => 'ID externo',
            'is_from_contact' => 'mensagem do contato',
            'reply_to_message_id' => 'mensagem de resposta',
        ];
    }
}
