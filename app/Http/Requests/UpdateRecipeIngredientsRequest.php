<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRecipeIngredientsRequest extends FormRequest
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
    public function rules()
    {
        return [
            'ingredients' => 'required|array',
            'ingredients.*.id' => 'required|integer',
            'ingredients.*.name' => 'required|string',
            'ingredients.*.quantity' => 'required|string',
            'ingredients.*.metric' => 'required|string',
        ];
    }
}
