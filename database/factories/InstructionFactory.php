<?php

namespace Database\Factories;

use App\Models\Instruction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Instruction>
 */
class InstructionFactory extends Factory
{
    protected $model = Instruction::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'instruction' => $this->faker->paragraph(),
            'step' => $this->faker->numberBetween(1, 10)
        ];
    }
}
