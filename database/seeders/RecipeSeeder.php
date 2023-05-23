<?php

namespace Database\Seeders;

use App\Models\RecipePicture;
use App\Models\Recipe;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecipeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('recipes')->truncate();
        DB::statement('ALTER TABLE recipes AUTO_INCREMENT = 1;');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        RecipePicture::factory()
            ->count(20)
            ->create();

        $pictures = RecipePicture::all()->shuffle();

        Recipe::factory()
            ->count(5)
            ->hasInstructions(7)
            ->hasIngredients(10)
            ->create()
            ->each(function ($recipe) use ($pictures) {
                $recipe->pictures()->saveMany($pictures->take(2));
            });

    }
}
