<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Ingredient;
use App\Models\Instruction;
use App\Models\Recipe;
use App\Models\RecipePicture;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;


class RecipeTest extends TestCase
{
use RefreshDatabase;

public function testGetIngredients()
{
$recipe = Recipe::factory()->create();
$ingredients = Ingredient::factory()->count(5)->create(['recipe_id' => $recipe->id]);

$response = $this->get("/api/v1/recipes/{$recipe->id}/ingredients");

$response->assertStatus(200)
->assertJson([
'links' => [
'self' => route('recipes.ingredients.index', ['recipe' => $recipe->id]),
'recipe' => route('recipes.show', ['recipe' => $recipe->id]),
],
]);
}

public function testGetRecipePictures()
{
$recipe = Recipe::factory()->create();
$recipePictures = RecipePicture::factory()->count(3)->create(['recipe_id' => $recipe->id]);

$response = $this->get("/api/v1/recipes/{$recipe->id}/recipe_pictures");

$response->assertStatus(200)
->assertJson([
'links' => [
'self' => route('recipes.recipe_pictures.index', ['recipe' => $recipe->id]),
'recipe' => route('recipes.show', ['recipe' => $recipe->id]),
],
]);
}

public function testGetInstructions()
{
$recipe = Recipe::factory()->create();
$instructions = Instruction::factory()->count(4)->create(['recipe_id' => $recipe->id]);

$response = $this->get("/api/v1/recipes/{$recipe->id}/instructions");

$response->assertStatus(200)
->assertJson([
'links' => [
'self' => route('recipes.instructions.index', ['recipe' => $recipe->id]),
'recipe' => route('recipes.show', ['recipe' => $recipe->id]),
],
]);
}

public function testSearchByIngredients()
{
$ingredientName = 'garlic';
$recipe = Recipe::factory()->create();
$ingredient = Ingredient::factory()->create(['recipe_id' => $recipe->id, 'name' => $ingredientName]);

$response = $this->get("/api/v1/recipes/search?ingredients={$ingredientName}");

$response->assertStatus(200)
->assertJsonCount(1)
->assertJsonFragment([
'name' => $recipe->name,
]);
}
}
