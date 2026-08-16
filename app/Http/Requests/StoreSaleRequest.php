<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->attributes->has('connectorClient');
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'source' => ['required', 'string', 'in:softrestaurant'],
            'folio' => ['required', 'integer', 'min:1'],
            'ticket' => ['required', 'integer', 'min:1'],
            'opened_at' => ['required', 'date'],
            'closed_at' => ['required', 'date', 'after_or_equal:opened_at'],
            'station' => ['nullable', 'string', 'max:100'],

            'customer' => ['nullable', 'array'],
            'customer.external_id' => ['required_with:customer', 'string', 'max:100'],
            'customer.name' => ['nullable', 'string', 'max:255'],

            'totals' => ['required', 'array'],
            'totals.subtotal' => ['required', 'numeric'],
            'totals.tax' => ['required', 'numeric'],
            'totals.total' => ['required', 'numeric'],
            'totals.tip' => ['required', 'numeric'],
            'totals.total_with_tip' => ['required', 'numeric'],

            'items' => ['required', 'array', 'min:1', 'max:500'],
            'items.*.product_id' => ['required', 'string', 'max:100'],
            'items.*.name' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric'],
            'items.*.unit_price' => ['required', 'numeric'],
            'items.*.discount' => ['required', 'numeric'],
            'items.*.modifier' => ['required', 'boolean'],
            'items.*.compound_id' => ['nullable', 'string', 'max:100'],
            'items.*.compound_main' => ['required', 'boolean'],

            'payments' => ['required', 'array', 'min:1', 'max:20'],
            'payments.*.method' => ['required', 'string', 'max:50'],
            'payments.*.amount' => ['required', 'numeric'],
            'payments.*.tip' => ['required', 'numeric'],
            'payments.*.reference' => ['nullable', 'string', 'max:255'],
        ];
    }
}
