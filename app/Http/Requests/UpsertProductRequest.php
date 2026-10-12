<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpsertProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'sku' => ['required', 'string', 'max:64'],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'url' => ['nullable', 'url', 'max:255'],
            'price' => ['required_without:price_minor', 'numeric', 'min:0'],
            'price_minor' => ['nullable', 'integer', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'brand' => ['nullable', 'string', 'max:80'],
            'gtin' => ['nullable', 'string', 'max:20'],
        ];
    }
}
