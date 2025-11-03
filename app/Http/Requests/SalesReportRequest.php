<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SalesReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'sku' => ['nullable', 'string'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'company_id.required' => 'The company ID is required.',
            'company_id.exists' => 'The selected company does not exist.',
            'start_date.required' => 'The start date is required.',
            'start_date.date_format' => 'The start date must be in Y-m-d format.',
            'end_date.required' => 'The end date is required.',
            'end_date.date_format' => 'The end date must be in Y-m-d format.',
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
        ];
    }
}

