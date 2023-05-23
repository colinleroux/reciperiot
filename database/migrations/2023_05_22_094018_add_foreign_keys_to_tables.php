<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('recipe_ingredients', function (Blueprint $table) {
            $table->dropForeign(['recipe_id']);
            $table->foreign('recipe_id')->references('id')->on('recipes')->onDelete('cascade')->unique('recipe_ingredients_recipe_id_foreign');
        });

        Schema::table('recipe_pictures', function (Blueprint $table) {
            $table->dropForeign(['recipe_id']);
            $table->foreign('recipe_id')->references('id')->on('recipes')->onDelete('cascade')->unique('recipe_pictures_recipe_id_foreign');
        });

        Schema::table('instructions', function (Blueprint $table) {
            $table->dropForeign(['recipe_id']);
            $table->foreign('recipe_id')->references('id')->on('recipes')->onDelete('cascade')->unique('recipe_instructions_recipe_id_foreign');
        });

        Schema::table('favourites', function (Blueprint $table) {
            $table->dropForeign(['recipe_id']);
            $table->foreign('recipe_id')->references('id')->on('recipes')->onDelete('cascade')->unique('favourites_recipe_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipe_ingredients,recipe_pictures,recipe_instructions,favourites', function (Blueprint $table) {
            //
        });
    }
};
