<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Favourite;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with 110 recipe pictures
     * 10 favourites - 550 ingredients - 635 instructions
     * 24 recipes - 11 users.
     */
    public function run(): void
    {
        $this->call([
            FavouriteSeeder::class,
            RecipePictureSeeder::class,
            IngredientSeeder::class,
            InstructionSeeder::class,
            RecipeSeeder::class,
            UserSeeder::class
        ]);
    }
}
