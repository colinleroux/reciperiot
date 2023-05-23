<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRecipePicturesRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'recipe_pictures' => 'required|array',
            'recipe_pictures.*.id' => 'integer',
            'recipe_pictures.*.filename' => 'required|string',
            'recipe_pictures.*.title' => 'required|string',
            'recipe_pictures.*.url' => 'required|string',
        ];
    }
}
