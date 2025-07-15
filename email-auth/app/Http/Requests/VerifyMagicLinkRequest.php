<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyMagicLinkRequest extends FormRequest
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
        return [
            'email' => ['required', 'email', 'max:255'],
            'token' => ['required', 'string', 'size:64'], // SHA256 hash is 64 characters
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.max' => 'Email address must not exceed 255 characters.',
            'token.required' => 'Magic link token is required.',
            'token.string' => 'Magic link token must be a string.',
            'token.size' => 'Invalid magic link token format.',
        ];
    }
}
