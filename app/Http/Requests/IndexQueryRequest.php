<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class IndexQueryRequest extends FormRequest
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
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort' => ['nullable', 'string'],
            'q' => ['nullable', 'string', 'max:100'],
            'filter' => ['sometimes', 'array'],
            'filter.*' => ['nullable'],
            'filter.price_min' => ['nullable', 'numeric'],
            'filter.price_max' => ['nullable', 'numeric'],
            'filter.car_id' => ['nullable', 'integer'],
            'filter.driver_id' => ['nullable', 'integer'],
            'filter.owner_id' => ['nullable', 'integer'],
            'filter.start_at_from' => ['nullable', 'date'],
            'filter.start_at_to' => ['nullable', 'date'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'data' => null,
            'message' => 'Validation failed.',
            'errors' => $validator->errors(),
        ], 422));
    }

    public function perPage(): int
    {
        return min(max((int) ($this->input('per_page', 15)), 1), 100);
    }

    /**
     * @return array{column:?string,direction:string}
     */
    public function sortFieldAndDirection(array $allowedSorts): array
    {
        $sort = $this->input('sort');
        if (!$sort) {
            return ['column' => null, 'direction' => 'asc'];
        }

        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $column = ltrim($sort, '-');

        if (!in_array($column, $allowedSorts, true)) {
            return ['column' => null, 'direction' => 'asc'];
        }

        return ['column' => $column, 'direction' => $direction];
    }

    public function filters(): array
    {
        return $this->input('filter', []);
    }

    public function search(): ?string
    {
        $q = $this->input('q');
        return $q !== null ? trim($q) : null;
    }
}
