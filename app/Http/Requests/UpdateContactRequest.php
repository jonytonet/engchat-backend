<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $contactId = $this->route('contact');

        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'nullable',
                'email',
                Rule::unique('contacts', 'email')->ignore($contactId)
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('contacts', 'phone')->ignore($contactId)
            ],
            'document' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('contacts', 'document')->ignore($contactId)
            ],
            'avatar' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'email.email' => 'O email deve ter um formato válido.',
            'email.unique' => 'Este email já está em uso.',
            'phone.unique' => 'Este telefone já está em uso.',
            'document.unique' => 'Este documento já está em uso.',
        ];
    }
}
