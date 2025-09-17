<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_code' => ['nullable', 'string', 'max:50'],
            'member_code' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'customer_type_id' => ['nullable', 'exists:customer_types,id'],
            'is_active' => ['boolean'],
            'store_id' => ['nullable', 'exists:stores,id'],
            'points' => ['nullable', 'integer', 'min:0'],
            'photo' => ['nullable', 'string'],
            'status' => ['nullable', 'in:active,inactive'],
            'joined_at' => ['nullable', 'date'],
        ];
    }
}
