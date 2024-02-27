<?php

namespace App\Http\Requests;

use App\Rules\ValidSlug;
use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return void
     */
    protected function prepareForValidation(): void
    {
        if ($this->categories && is_string($this->categories)) {
            $this->merge([
                'categories' => explode(',', $this->categories),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'price' => 'required|integer|min:5|max:4294967295',
            'quantity' => 'required|integer|min:1|max:10000',
            'slug' => [
                'required',
                'string',
                'max:255',
                "unique:products,slug",
                new ValidSlug,
            ],
            'thumbnail' => 'required|image|mimes:jpg,png,jpeg|max:2048',
            'status' => 'boolean',
            'categories' => 'required|array',
            'categories.*' => 'required|exists:categories,id'
        ];
    }
}
