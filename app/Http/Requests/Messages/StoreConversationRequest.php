<?php

namespace App\Http\Requests\Messages;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'recipient_id' => ['nullable', 'integer', 'exists:users,id'],
            'property_id' => ['nullable', 'integer', 'exists:properties,id'],
            'body' => ['required', 'string', 'max:2000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $user = $this->user();

            if ($user === null) {
                return;
            }

            if ($user->role === 'user' && ! $this->filled('property_id')) {
                $validator->errors()->add('property_id', 'Please select a property to contact its owner or agent.');
            }

            if (in_array($user->role, ['owner', 'agent'], true) && ! $this->filled('recipient_id') && ! $this->filled('property_id')) {
                $validator->errors()->add('recipient_id', 'Please choose support or a recipient.');
            }

            if ($user->role === 'admin' && ! $this->filled('recipient_id')) {
                $validator->errors()->add('recipient_id', 'Please select a user to message.');
            }
        });
    }
}
