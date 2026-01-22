<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitDriverKycRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'license_front' => ['required', 'image', 'max:5120'],
            'license_back' => ['required', 'image', 'max:5120'],
            'nrc_front' => ['required', 'image', 'max:5120'],
            'nrc_back' => ['required', 'image', 'max:5120'],
            'selfie' => ['required', 'image', 'max:5120'],
            'other_files' => ['sometimes', 'array'],
            'other_files.*' => ['file', 'max:8192'],
        ];
    }
}
