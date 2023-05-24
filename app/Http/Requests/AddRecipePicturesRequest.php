<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddRecipePicturesRequest extends FormRequest
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
       // return [
          //  'image' => ['required', 'image'], // Change 'pictures' to 'image' and validate as an image
        //    'title' => ['required'],
      //  ];
        return [
            'images' => ['required', 'array'],
            'images.*' => ['required', 'image:jpeg,png,jpg,gif,svg', 'max:2048'],
        ];
    }
}
