<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'isbn' => [
                'nullable',
                'string',
                'regex:/^[0-9]+$/',
                'unique:books,isbn',
            ],

            'value' => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The book name is required.',
            'isbn.regex' => 'The ISBN must contain numbers only.',
            'isbn.unique' => 'This ISBN is already registered.',
            'value.numeric' => 'The book value must be numeric.',
            'value.min' => 'The book value cannot be negative.',
        ];
    }
}
