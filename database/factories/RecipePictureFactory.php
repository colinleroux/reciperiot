<?php

namespace Database\Factories;

use App\Models\Recipe;
use App\Models\RecipePicture;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class RecipePictureFactory extends Factory
{
    protected $model = RecipePicture::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'recipe_id' => Recipe::factory(),
            'filename' => $this->faker->imageUrl(),
            'title' => $this->faker->word(),
            'url' => $this->faker->word(),
        ];
    }
}
