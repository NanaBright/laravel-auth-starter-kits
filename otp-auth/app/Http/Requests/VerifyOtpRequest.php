<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $otpLength = config('otp.code.length', 6);

        return [
            'identifier' => ['required', 'string'], // email or phone
            'code' => ['required', 'string', "size:{$otpLength}"],
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        $otpLength = config('otp.code.length', 6);

        return [
            'identifier.required' => 'Email or phone number is required.',
            'code.required' => 'Verification code is required.',
            'code.size' => "Verification code must be {$otpLength} characters.",
        ];
    }
}
