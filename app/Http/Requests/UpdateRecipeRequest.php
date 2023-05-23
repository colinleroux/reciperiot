<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRecipeRequest extends FormRequest
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
        $method = $this->getMethod();

        if ($method === 'PUT') {
            return [
                'title' => ['required', 'max:255'],
                'slug' => ['required'],
                'description' => ['required'],
                'notes' => ['required'],
                'user_id' => ['required'],
                'difficulty' => ['required'],
                'instructions' => ['required', 'array'],
                'instructions.*.step' => ['required', 'integer'],
                'instructions.*.description' => ['required'],
                'ingredients' => ['required', 'array'],
                'ingredients.*.name' => ['required'],
                'ingredients.*.quantity' => ['required'],
                'pictures' => ['required', 'array'],
                'pictures.*.url' => ['required', 'url'],
            ];
        } else if ($method === 'PATCH') {
            return [
                'title' => ['sometimes', 'required', 'max:255'],
                'slug' => ['sometimes', 'required'],
                'description' => ['sometimes', 'required'],
                'notes' => ['sometimes', 'required'],
                'user_id' => ['sometimes', 'required'],
                'difficulty' => ['sometimes', 'required'],
                'instructions' => ['sometimes', 'array'],
                'instructions.*.step' => ['sometimes', 'required', 'integer'],
                'instructions.*.description' => ['sometimes', 'required'],
                'ingredients' => ['sometimes', 'array'],
                'ingredients.*.name' => ['sometimes', 'required'],
                'ingredients.*.quantity' => ['sometimes', 'required'],
                'pictures' => ['sometimes', 'array'],
                'pictures.*.url' => ['sometimes', 'required', 'url'],
            ];
        }
        return [];
    }
};
