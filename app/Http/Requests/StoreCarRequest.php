<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCarRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'make' => ['required', 'string', 'max:255'],
            'model' => ['required', 'string', 'max:255'],
            'year' => ['required', 'integer', 'between:1980,' . (date('Y') + 1)],
            'plate_number' => ['required', 'string', 'max:50'],
            'daily_price' => ['required', 'numeric', 'min:0'],
            'deposit_amount' => ['nullable', 'numeric', 'min:0'],
            'pickup_lat' => ['required', 'numeric', 'between:-90,90'],
            'pickup_lng' => ['required', 'numeric', 'between:-180,180'],
            'description' => ['sometimes', 'string'],
            'photos' => ['required', 'array', 'min:3'],
            'photos.*' => ['image'],
            'documents' => ['sometimes', 'array'],
            'documents.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:8192'],
            'doc_types' => ['sometimes', 'array'],
            'doc_types.*' => ['string', 'max:50'],
        ];
    }
}
