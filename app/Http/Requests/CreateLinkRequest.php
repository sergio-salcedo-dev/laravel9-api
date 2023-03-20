<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateLinkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool)Auth::user();
//        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'link' => 'required|url|unique:links,full_link',
        ];
    }


    /**
     * Custom message for validation
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'link.unique' => 'The link is already saved',
        ];
    }
}
