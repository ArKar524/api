<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTripApplicationRequest extends FormRequest
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
            'status' => ['sometimes', 'in:active,completed,dispute,cancelled'],
            'start_at' => ['sometimes', 'date'],
            'end_at' => ['sometimes', 'date', 'after:start_at'],
            'total_amount' => ['sometimes', 'numeric', 'min:0'],
            'pickup_location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'dropoff_location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'contract_terms' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
