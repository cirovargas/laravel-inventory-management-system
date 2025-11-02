<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class GetSalesReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_date' => ['required', 'date', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'sku' => ['nullable', 'string', 'exists:products,sku'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'start_date.required' => 'The start date is required.',
            'start_date.date_format' => 'The start date must be in Y-m-d format.',
            'end_date.required' => 'The end date is required.',
            'end_date.date_format' => 'The end date must be in Y-m-d format.',
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
            'sku.exists' => 'The selected SKU does not exist.',
        ];
    }
}

