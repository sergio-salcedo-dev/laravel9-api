<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSellsProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
//        return !!Auth::user();
        return true;
    }

    /** Get the validation rules that apply to the request. */
    public function rules(): array
    {
        return [
            "storeId" => "required|numeric|integer|gt:0",
            "productId" => "required|numeric|integer|gt:0",
        ];
    }

    /** Custom message for validation */
    public function messages(): array
    {
        return [
            'storeId.required' => 'the storeId field is required',
            'productId.required' => 'the productId field is required',
        ];
    }
}
