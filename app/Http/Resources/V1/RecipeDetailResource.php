<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

class RecipeDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Return full recipe payload for detailed app display
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'notes' => $this->notes,
            'difficulty' => $this->difficulty,
            'user_id' => $this->user_id,
            'ingredients' => IngredientResource::collection($this->ingredients),
            'recipe_pictures' => RecipePictureResource::collection($this->pictures),
            'instructions' => InstructionResource::collection($this->instructions),
            'links' => [
                'self' => URL::current(),
              //  'user' => URL::route('users.show', ['user' => $this->user_id]),
                'ingredients' => URL::route('recipes.ingredients.index', ['recipe' => $this->id]),
                'recipe_pictures' => URL::route('recipes.recipe_pictures.index', ['recipe' => $this->id]),
                'instructions' => URL::route('recipes.instructions.index', ['recipe' => $this->id]),
            ],
        ];
    }
}
