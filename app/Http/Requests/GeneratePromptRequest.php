<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class GeneratePromptRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'image' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg,gif,svg',
                'min:1', // 1 KB
                'max:10240', // 10MB
                'dimensions:min_width=100,min_height=100,max_width=10240,max_height=10240',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => 'The image field is required.',
            'image.image' => 'The image must be an image.',
            'image.mimes' => 'The image must be a valid image format.',
            'image.min' => 'The image must be greater than 1KB.',
            'image.max' => 'The image must be less than 10MB.',
            'image.dimensions' => 'The image must be between 100x100 and 10240x10240 pixels.',
        ];
    }
}
