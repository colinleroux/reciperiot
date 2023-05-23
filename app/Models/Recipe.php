<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'slug',
        'description',
        'notes',
        'difficulty',
        'time',
        'user_id'
    ];
    public function instructions()
    {
        return $this->hasMany(Instruction::class);
    }

    public function ingredients()
    {
        return $this->hasMany(Ingredient::class);
    }

    public function pictures()
    {
        return $this->hasMany(RecipePicture::class);
    }

    public function favourites()
    {
        return $this->belongsToMany(User::class, 'favourites');
    }
}

