<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recipe_pictures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recipe_id');
            $table->string('filename')->default(' ');
            $table->string('title')->default(' ');
            $table->string('url')->default(' ');
            $table->timestamps();

            $table->foreign('recipe_id')
                ->references('id')
                ->on('recipes')
                ->onDelete('cascade');
        });

        // Set the starting point for the id field of recipe_pictures to 1
        DB::statement('ALTER TABLE recipe_pictures AUTO_INCREMENT = 1;');
    }
};
