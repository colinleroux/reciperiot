<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IngredientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $recipes = Recipe::all();

        foreach ($recipes as $recipe) {
            Ingredient::factory()
                ->count(5)
                ->for($recipe)
                ->create();
        }
    }
}
