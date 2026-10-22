<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** Clean the input first, so "same number, different format" cannot slip past the unique rule. */
    protected function prepareForValidation(): void
    {
        $phone = (string) $this->input('phone');

        $this->merge([
            'name' => trim(preg_replace('/\s+/', ' ', (string) $this->input('name'))),
            'email' => Str::lower(trim((string) $this->input('email'))),
            'phone' => Phone::normalize($phone) ?? trim($phone),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'string', 'email:rfc', 'max:255', Rule::unique(User::class, 'email')],
            'phone' => ['required', 'string', 'regex:/^0[79]\d{8}$/', Rule::unique(User::class, 'phone')],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'An account with this email already exists. Try logging in instead.',
            'phone.regex' => 'Enter a valid Ethiopian mobile number, for example 0911 234 567.',
            'phone.unique' => 'An account with this phone number already exists.',
        ];
    }
}
