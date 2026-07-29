<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBlogRequest extends FormRequest
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
            //
            'slug' => 'sometimes|required|string|unique:blogs,slug,' . $this->route('blog')->id,
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'author_id' => 'sometimes|required|integer|exists:users,id',
        ];
    }
}
