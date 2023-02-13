<?php

declare(strict_types=1);

namespace App\Http\Requests;

class StoreUpdateOrCreateRequest extends BaseFormRequest
{
    private const LIMIT_PRODUCTS_INSERTION = 10;
    private const REQUEST_KEY_PRODUCT_IDS = 'productIds';
    private const REQUEST_KEY_PRODUCT_IDS_ASTERISK = 'productIds.*';
    private const REQUEST_KEY_PRODUCTS = 'products';
    private const REQUEST_KEY_PRODUCTS_ASTERISK = 'products.*';

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
//        return !!Auth::user();
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            "name" => "required|string|max:200",
            self::REQUEST_KEY_PRODUCT_IDS => $this->getRequestKeyProductIdsRules(),
            self::REQUEST_KEY_PRODUCT_IDS_ASTERISK => 'numeric|integer|gt:0',
            self::REQUEST_KEY_PRODUCTS => "array|nullable",
            self::REQUEST_KEY_PRODUCTS_ASTERISK . '.id' => 'required|numeric|integer|gt:0', // 'products.*.id'
            self::REQUEST_KEY_PRODUCTS_ASTERISK . '.stock' => 'nullable|numeric|integer|gt:-1', // 'products.*.stock'
        ];
    }

    /**
     * Custom message for validation
     */
    public function messages(): array
    {
        return [
            self::REQUEST_KEY_PRODUCT_IDS_ASTERISK => $this->getRequestKeyProductIdsMessage(),
            self::REQUEST_KEY_PRODUCTS_ASTERISK . '.id' => "Invalid data for object key 'id', product_id: integer > 0",
            self::REQUEST_KEY_PRODUCTS_ASTERISK . '.stock' => "Invalid data for object key 'stock', stock: integer >= 0",
        ];
    }

    private function getRequestKeyProductIdsRules(): string
    {
        return 'array|nullable|between:1,' . self::LIMIT_PRODUCTS_INSERTION;
    }

    private function getRequestKeyProductIdsMessage(): string
    {
        return "the field " . self::REQUEST_KEY_PRODUCT_IDS . " must be an array of integers > 0, from 1 to " . self::LIMIT_PRODUCTS_INSERTION . " elements";
    }
}
