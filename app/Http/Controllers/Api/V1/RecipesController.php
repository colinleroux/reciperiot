<?php

namespace App\Http\Controllers\Api\V1;
use App\Traits\HttpResponses;
use App\Http\Controllers\Api\BaseController as BaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddRecipeIngredientsRequest;
use App\Http\Requests\AddRecipeInstructionsRequest;
use App\Http\Requests\AddRecipePicturesRequest;
use App\Http\Requests\StoreRecipeRequest;
use App\Http\Requests\UpdateRecipeIngredientsRequest;
use App\Http\Requests\UpdateRecipeInstructionsRequest;
use App\Http\Requests\UpdateRecipePicturesRequest;
use App\Http\Requests\UpdateRecipeRequest;
use App\Http\Resources\V1\FavoritesResource;
use App\Http\Resources\V1\IngredientResource;
use App\Http\Resources\V1\InstructionResource;
use App\Http\Resources\V1\RecipeCollection;
use App\Http\Resources\V1\RecipeDetailResource;
use App\Http\Resources\V1\RecipePictureResource;
use App\Http\Resources\V1\RecipeResource;
use App\Models\Ingredient;
use App\Models\Instruction;
use App\Models\Recipe;
use App\Models\RecipePicture;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Stmt\Return_;
use Illuminate\Http\JsonResponse;
use function PHPUnit\Framework\isNull;

class RecipesController extends BaseController
{
    use HttpResponses;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return new RecipeCollection(Recipe::all());
    }
    /**
     * Display a detailed listing of the recipes for specific user.
     */
    public function indexByUser(Request $request, User $user): AnonymousResourceCollection
    {
        $perPage = $request->query('per_page', 10); // Number of records per page (default: 10)
        $recipes = Recipe::where('user_id', $user->id)->paginate($perPage);

        if ($recipes->isEmpty()) {
            return new AnonymousResourceCollection([], RecipeDetailResource::class);
        }

        return RecipeDetailResource::collection($recipes);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRecipeRequest $request)
    {
        $validatedData = $request->validated();

        // Create the recipe
        $recipe = Recipe::create([
            'title' => $validatedData['title'],
            'slug' => $validatedData['slug'],
            'description' => $validatedData['description'],
            'notes' => $validatedData['notes'],
            'user_id' => $validatedData['user_id'],
            'difficulty' => $validatedData['difficulty'],
            'time' => $validatedData['time'],
        ]);

        // Return the created recipe resource
        return new RecipeResource($recipe);
    }

    public function addInstructions(AddRecipeInstructionsRequest $request, Recipe $recipe)
    {
        $validatedData = $request->validated();

        // Add instructions to the recipe
        $instructions = [];
        foreach ($validatedData['instructions'] as $instructionData) {
            $instruction = $recipe->instructions()->create([
                'instruction' => $instructionData['instruction'],
                'step' => $instructionData['step'],
                'description' => $instructionData['description'],
            ]);
            $instructions[] = $instruction;
        }

        // Return the updated recipe resource
        return new RecipeResource($recipe->refresh());
    }

    public function addIngredients(AddRecipeIngredientsRequest $request, Recipe $recipe)
    {
        $validatedData = $request->validated();

        // Add ingredients to the recipe
        $ingredients = [];
        foreach ($validatedData['ingredients'] as $ingredientData) {
            $ingredient = $recipe->ingredients()->create([
                'name' => $ingredientData['name'],
                'quantity' => $ingredientData['quantity'],
                'metric' => $ingredientData['metric'],
            ]);
            $ingredients[] = $ingredient;
        }

        // Return the updated recipe resource
        return new RecipeResource($recipe->refresh());
    }

   // public function addPictures(AddRecipePicturesRequest $request, Recipe $recipe)
    public function addPictures(AddRecipePicturesRequest $request, Recipe $recipe)
    {
        // Validation is handled by the AddRecipePicturesRequest class

        $uploadFolder = 'users';
        $uploadedImages = [];

        foreach ($request->file('images') as $image) {
            // Generate a unique filename for each image
            $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $image->getClientOriginalExtension();
            $recipeId = $recipe->id;
            $userId = auth()->user()->id;
            $filename = $recipeId . '-' . $userId . '-' . $originalFilename . '-' . time() . '.' . $extension;

            $image_uploaded_path = $image->storeAs($uploadFolder, $filename, 'public');

            // Find the corresponding recipe
            $recipe = Recipe::findOrFail($recipeId);

            // Create the picture record in the database
            $picture = $recipe->pictures()->create([
                'filename' => $filename,
                'title' => $request->title,
                'url' => Storage::disk('public')->url($image_uploaded_path),
            ]);

            $uploadedImages[] = [
                "recipe_id" => $recipeId,
                "user_id" => $userId,
                "image_id" => $picture->id,
                "image_name" => $filename,
                "image_url" => $picture->url,
                "mime" => $image->getClientMimeType()
            ];
        }

        return $this->success($uploadedImages, 'Files Uploaded Successfully', 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Recipe $recipe)
    {
        $recipe->load('ingredients', 'pictures', 'instructions');
        return new RecipeDetailResource($recipe);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRecipeRequest $request, Recipe $recipe)
    {

        $validatedData = $request->validated();

        // Update recipe details
        $recipe->update($validatedData);

        // Return the updated recipe resource
        return new RecipeResource($recipe->refresh());
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    public function getIngredients(Request $request, $recipeId)
    {
        $recipe = Recipe::find($recipeId);

        if (!$recipe) {
            return response()->json(['message' => "Recipe $recipeId not found."], 404);
        }

        $ingredients = $recipe->ingredients;

        if ($ingredients->isEmpty()) {
            return response()->json(['message' => "No ingredients found for this recipe $recipeId."], 404);
        }

        return IngredientResource::collection($ingredients)->additional([
            'links' => [
                'self' => URL::route('recipes.ingredients.index', ['recipe' => $recipeId]),
                'recipe' => URL::route('recipes.show', ['recipe' => $recipeId]),
            ]
        ]);
    }
    public function getFavourites($user_id)
    {
        $favourites = Recipe::whereHas('favourites', function ($query) use ($user_id) {
            $query->where('user_id', $user_id);
        })->get();
        return FavoritesResource::collection($favourites);

    }

    public function getRecipePictures(Request $request, $recipeId)
    {
        $recipe = Recipe::find($recipeId);

        if (!$recipe) {
            return response()->json(['message' => "Recipe $recipeId not found."], 404);
        }

        $recipePictures = $recipe->pictures;

        if ($recipePictures->isEmpty()) {
            return response()->json(['message' => "No recipe pictures found for Recipe $recipeId."], 404);
        }

        return RecipePictureResource::collection($recipePictures)->additional([
            'links' => [
                'self' => URL::route('recipes.recipe_pictures.index', ['recipe' => $recipeId]),
                'recipe' => URL::route('recipes.show', ['recipe' => $recipeId]),
            ]
        ]);
    }


    public function getInstructions(Request $request, $recipeId)
    {
        $recipe = Recipe::find($recipeId);

        if (!$recipe) {
            return response()->json(['message' => "Recipe $recipeId not found."], 404);
        }

        $instructions = $recipe->instructions;

        if ($instructions->isEmpty()) {
            return response()->json(['message' => "No instructions found for Recipe $recipeId."], 404);
        }

        return InstructionResource::collection($instructions)->additional([
            'links' => [
                'self' => URL::route('recipes.instructions.index', ['recipe' => $recipeId]),
                'recipe' => URL::route('recipes.show', ['recipe' => $recipeId]),
            ]
        ]);
    }
        /* Search for recipe by single ingredient */
        public function searchByIngredients(Request $request)
    {
        $query = $request->query('ingredients');

        $recipes = Recipe::whereHas('ingredients', function ($queryBuilder) use ($query) {
            $queryBuilder->where('name', 'LIKE', '%' . $query . '%');
        })->get();

        if ($recipes->isEmpty()) {
            return response()->json(['message' => 'No recipes found for that ingredient.'], 404);
        }

        return RecipeResource::collection($recipes);
    }

    /* Search for recipes using multiple ingredients */

    public function searchByManyIngredients(Request $request)
    {
        $ingredients = $request->query('ingredients');

        // Convert the ingredients array to lowercase for case-insensitive searching
        $ingredients = array_map('strtolower', $ingredients);

        $recipes = Recipe::whereHas('ingredients', function ($queryBuilder) use ($ingredients) {
            $queryBuilder->whereIn('name', $ingredients);
        })->get();

        if ($recipes->isEmpty()) {
            return response()->json(['message' => 'No recipes found for the specified ingredients.'], 404);
        }

        return RecipeResource::collection($recipes);
    }

    public function updateInstructions(UpdateRecipeInstructionsRequest $request, Recipe $recipe)
    {
        $validatedData = $request->validated();
        $updatesMade = false;

        if (isset($validatedData['instructions'])) {
            foreach ($validatedData['instructions'] as $instructionData) {
                if (isset($instructionData['id'])) {
                    $instruction = Instruction::where('id', $instructionData['id'])
                        ->where('recipe_id', $recipe->id)
                        ->first();
                    if ($instruction) {
                        $instruction->instruction = $instructionData['instruction'];
                        $instruction->step = $instructionData['step'];
                        $instruction->save();
                        $updatesMade = true;
                    }
                }
            }
        }

        if (!$updatesMade) {
            return response()->json(['message' => 'No updates were made.'], 200);
        }


    // Return the updated recipe resource
        return new RecipeResource($recipe->refresh());
    }
    public function updatePictures(UpdateRecipePicturesRequest $request, $recipeid)
    {
        $validatedData = $request->validated();
        $pictureIds = collect($validatedData['recipe_pictures'])->pluck('id');

        // Find the recipe by the specified ID
        $recipe = Recipe::findOrFail($recipeid);

        // Get the pictures that belong to the specified recipe and have the specified IDs
        $pictures = $recipe->pictures()->whereIn('id', $pictureIds)->get();

        // Update the pictures with the new data
        foreach ($pictures as $picture) {
            $pictureData = collect($validatedData['recipe_pictures'])
                ->firstWhere('id', $picture->id);

            $picture->filename = $pictureData['filename'];
            $picture->title = $pictureData['title'];
            $picture->url = $pictureData['url'];
            $picture->save();
        }

        // Return the updated recipe resource
        return new RecipeResource($recipe->refresh());
    }

    public function updateIngredients(UpdateRecipeIngredientsRequest $request, Recipe $recipe)
    {
        $validatedData = $request->validated();
        $updatesMade = false;

        if (isset($validatedData['ingredients'])) {
            foreach ($validatedData['ingredients'] as $ingredientData) {
                if (isset($ingredientData['id'])) {
                    $ingredient = Ingredient::where('id', $ingredientData['id'])
                        ->where('recipe_id', $recipe->id)
                        ->first();

                    if ($ingredient) {
                        $ingredient->name = $ingredientData['name'];
                        $ingredient->quantity = $ingredientData['quantity'];
                        $ingredient->metric = $ingredientData['metric'];
                        $ingredient->save();
                        $updatesMade = true;
                    }
                }
            }
        }

        if ($updatesMade) {
            // Return the updated recipe resource
            return new RecipeResource($recipe->refresh());
        } else {
            // Return a response indicating no updates were made
            return response()->json(['message' => 'No updates were made for the requested recipe.'], 200);
        }
    }
    public function deleteRecipe($id)
    {
        // Find the recipe by ID
        $recipe = Recipe::findOrFail($id);

        // Delete the recipe's pictures
        $recipe->pictures()->delete();

        // Delete the recipe's instructions
        $recipe->instructions()->delete();

        // Delete the recipe's ingredients
        $recipe->ingredients()->delete();

        // Delete the recipe itself
        $recipe->delete();

        return response()->json(['message' => 'Recipe deleted successfully.'], 200);
    }
    /**
     * Delete a picture for a recipe.
     *
     * @param Request $request
     * @param Recipe $recipe
     * @return \Illuminate\Http\JsonResponse
     */
    public function deletePicture(Request $request, Recipe $recipe)
    {
        $pictureId = $request->input('picture_id');

        // Find the picture by ID
        $picture = RecipePicture::find($pictureId);

        // Check if the picture exists and belongs to the recipe
        if (!$picture || $picture->recipe_id !== $recipe->id) {
            return $this->error('Picture not found for the given recipe.', 'error', 404);
        }

        // Delete the picture from storage
        //Storage::disk('public')->delete($picture->filename);
        Storage::disk('public')->delete('users/' . $picture->filename);
        // Delete the picture from the database
        $picture->delete();

        return $this->success(null, 'Picture deleted successfully.', 200);
    }
    public function deleteInstruction(Request $request, $recipeId)
    {
        // Find the instruction by ID
        $instruction = Instruction::find($request->input('instruction_id'));

        $recipeId = intval($recipeId);
        // Check if the instruction exists and belongs to the recipe
        if (!$instruction || $instruction->recipe_id !== $recipeId) {
        // if ($instruction->recipe_id !== $recipeId) {
            return $this->error('Instruction not found for the given recipe.', 'error', 404);
        }

        // Delete the instruction from the database
        $instruction->delete();

        return $this->success(null, 'Instruction deleted successfully.', 200);
    }
}
