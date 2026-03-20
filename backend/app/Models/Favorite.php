<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'place_id',
        'name',
        'address',
        'phone',
        'price_level',
        'rating',
        'user_ratings_total',
        'photo_url',
        'lat',
        'lng',
    ];

    protected $casts = [
        'price_level'        => 'integer',
        'rating'             => 'float',
        'user_ratings_total' => 'integer',
        'lat'                => 'float',
        'lng'                => 'float',
    ];
}
