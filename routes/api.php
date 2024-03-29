<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\V1\RecipesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Auth\Events\Verified;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Recipe routes for CRUD actions
|
| User routes for login/register/logout
|
|
*/
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
// Unauthorised Routes
////Reset password
Route::post('password/reset/{token}', [AuthController::class, 'resetPassword'])->name('password.reset');
Route::post('email/resend-verification', [AuthController::class, 'resendVerificationEmail'])->name('verification.resend');

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
// Used after registration - route for accepting email link to verify users email address
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verify'])->name('verification.verify');
Route::post('/password/email', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');

//Protected Routes

Route::group(['prefix'=>'v1', 'namespace' => 'App\Http\Controllers\Api\V1', 'middleware' => ['auth:sanctum']], function() {
// ROUTES FOR  RECIPES
    Route::get('recipes/search', [RecipesController::class, 'search'])->name('recipes.search');
// Route for retrieving recipes with provided ingredients(many) (search by ingredients)
    Route::get('recipes/multiple', [RecipesController::class, 'searchByManyIngredients'])->name('recipes.searchByManyIngredients');
// Route for retrieving all recipes
    Route::get('recipes', [RecipesController::class, 'index'])->name('recipes.index');
// Route for retrieving all users favourite recipes {userid}
    Route::get('/recipes/favourites/{user_id}', [RecipesController::class, 'getFavourites'])->name('recipes.favourite');
// Route for retrieving a recipe by id
    Route::get('recipes/{recipe}', [RecipesController::class, 'show'])->name('recipes.show');
// Route for retrieving a recipes ingredients by recipeid
    Route::get('recipes/{recipe}/ingredients', [RecipesController::class, 'getIngredients'])->name('recipes.ingredients.index');
// Route for retrieving a recipes pictures by recipeid
    Route::get('recipes/{recipe}/recipe_pictures', [RecipesController::class, 'getRecipePictures'])->name('recipes.recipe_pictures.index');
// Route for retrieving a recipes instructions by recipeid
    Route::get('recipes/{recipe}/instructions', [RecipesController::class, 'getInstructions'])->name('recipes.instructions.index');
// Route for retrieving recipes with provided ingredient (search by ingredient)
    Route::get('recipes', [RecipesController::class, 'searchByIngredients'])->name('recipes.searchByIngredients');

    // Route for retrieving recipes belonging to a userid
    Route::get('recipes/user/{user}', [RecipesController::class, 'indexByUser'])->name('recipes.indexByUser');
// Route for creating a basic recipe
    Route::post('/recipes', [RecipesController::class, 'store'])->name('recipes.store');
// Route for adding instructions to a recipe
    Route::post('/recipes/{recipe}/instructions', [RecipesController::class, 'addInstructions'])->name('recipes.addInstructions');
// Route for adding ingredients to a recipe
    Route::post('/recipes/{recipe}/ingredients', [RecipesController::class, 'addIngredients'])->name('recipes.addIngredients');
// Route for adding pictures to a recipe
    Route::post('/recipes/{recipe}/pictures', [RecipesController::class, 'addPictures'])->name('recipes.addPictures');
// Update entire recipe using PUT method
    Route::put('recipes/{recipe}', [RecipesController::class, 'update'])->name('recipes.update');
// Update a recipes ingredients using PUT method
    Route::patch('/recipes/{recipe}/ingredients', [RecipesController::class, 'updateIngredients'])->name('recipes.ingredients.update');
// Update a recipes pictures using PUT method
    Route::patch('/recipes/{recipe}/pictures', [RecipesController::class, 'updatePictures'])->name('recipes.pictures.update');
// Update a recipes instructions using PUT method
    Route::patch('/recipes/{recipe}/instructions', [RecipesController::class, 'updateInstructions'])->name('recipes.instructions.update');
// Update a recipe using PATCH method
    Route::patch('recipes/{recipe}', [RecipesController::class, 'update'])->name('recipes.update');
// Delete a recipe and all associated ingredients/instructions/pictures
    Route::delete('/recipes/delete/{id}',  [RecipesController::class,'deleteRecipe'])->name('recipes.delete');
// Delete a recipe picture for a recipe
    Route::delete('/recipes/{recipe}/pictures', [RecipesController::class, 'deletePicture'])->name('recipes.pictures.delete');
// Delete a recipe instruction for a recipe
    Route::delete('/recipes/{recipe}/instructions', [RecipesController::class, 'deleteInstruction'])->name('recipes.pictures.delete');


    //USER ROUTES
// Logout user - delete token
    Route::post('/logout', [AuthController::class, 'logout']);

});

// User change password API
Route::post('/change-password', [AuthController::class, 'changePassword'])->middleware('auth:sanctum');
