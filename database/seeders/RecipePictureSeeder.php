<?php

namespace Database\Seeders;

use App\Models\RecipePicture;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecipePictureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('recipe_pictures')->truncate();
        RecipePicture::factory()
            ->count(90)->create();
    }
}
