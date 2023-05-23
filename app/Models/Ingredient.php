<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'quantity',
        'metric'
    ];

    public function recipe()
    {
        return $this->belongsTo(recipe::class);
    }
}
