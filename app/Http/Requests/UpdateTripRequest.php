<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTripRequest extends FormRequest
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
        $ownerId = $this->user()?->id;

        return [
            'car_id' => [
                'sometimes',
                'integer',
                Rule::exists('cars', 'id')->where(function ($query) use ($ownerId) {
                    $query->where('owner_id', $ownerId)
                        ->where('status', 'active')
                        ->where('approval_status', 'approved');
                }),
            ],
            'start_date' => ['sometimes', 'date', 'after_or_equal:today'],
            'end_date' => ['sometimes', 'date', 'after:start_date'],
            'daily_rate' => ['sometimes', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'nullable', 'string', 'size:3'],
            'pickup_location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'dropoff_location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'status' => ['sometimes', 'in:pending,cancelled'],
        ];
    }
}
