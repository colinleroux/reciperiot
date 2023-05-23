<?php

namespace Database\Seeders;

use App\Models\Instruction;
use App\Models\Recipe;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InstructionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $recipes = Recipe::all();

        foreach ($recipes as $recipe) {
            $currentStep = 0; // initialize the current step to 0

            Instruction::factory()
                ->count(6)
                ->for($recipe)
                ->state([
                    'step' => function () use (&$currentStep) {
                        return ++$currentStep; // increment and return the current step
                    },
                ])
                ->create();
        }
    }
}
