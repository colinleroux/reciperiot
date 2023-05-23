<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\V1\RecipesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
// Unauthorised Routes

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

//Protected Routes

Route::group(['prefix'=>'v1', 'namespace' => 'App\Http\Controllers\Api\V1', 'middleware' => ['auth:sanctum']], function() {

    Route::get('recipes', [RecipesController::class, 'index'])->name('recipes.index');
    Route::get('recipes/{recipe}', [RecipesController::class, 'show'])->name('recipes.show');
    Route::get('recipes/{recipe}/ingredients', [RecipesController::class, 'getIngredients'])->name('recipes.ingredients.index');
    Route::get('recipes/{recipe}/recipe_pictures', [RecipesController::class, 'getRecipePictures'])->name('recipes.recipe_pictures.index');
    Route::get('recipes/{recipe}/instructions', [RecipesController::class, 'getInstructions'])->name('recipes.instructions.index');
    Route::get('recipes', [RecipesController::class, 'searchByIngredients'])->name('recipes.searchByIngredients');
    Route::get('recipes/user/{user}', [RecipesController::class, 'indexByUser'])->name('recipes.indexByUser');

    // Route for creating a basic recipe
    Route::post('/recipes', [RecipesController::class, 'store'])->name('recipes.store');

// Route for adding instructions to a recipe
    Route::post('/recipes/{recipe}/instructions', [RecipesController::class, 'addInstructions'])->name('recipes.addInstructions');

// Route for adding ingredients to a recipe
    Route::post('/recipes/{recipe}/ingredients', [RecipesController::class, 'addIngredients'])->name('recipes.addIngredients');

// Route for adding pictures to a recipe
    Route::post('/recipes/{recipe}/pictures', [RecipesController::class, 'addPictures'])->name('recipes.addPictures');
// Update a recipe using PUT method
    Route::put('recipes/{recipe}', [RecipesController::class, 'update'])->name('recipes.update');
    Route::patch('/recipes/{recipe}/ingredients', [RecipesController::class, 'updateIngredients'])->name('recipes.ingredients.update');
    Route::patch('/recipes/{recipe}/pictures', [RecipesController::class, 'updatePictures'])->name('recipes.pictures.update');
    Route::patch('/recipes/{recipe}/instructions', [RecipesController::class, 'updateInstructions'])->name('recipes.instructions.update');
// Update a recipe using PATCH method
    Route::patch('recipes/{recipe}', [RecipesController::class, 'update'])->name('recipes.update');
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/recipes/favourites/{user_id}', [RecipesController::class, 'getFavourites'])->name('recipes.favourite');
    Route::delete('/recipes/delete/{id}',  [RecipesController::class,'deleteRecipe'])->name('recipes.delete');
});

