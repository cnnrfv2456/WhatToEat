<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
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
        'distance',
        'photo_url',
        'lat',
        'lng',
    ];

    protected $casts = [
        'price_level'        => 'integer',
        'rating'             => 'float',
        'user_ratings_total' => 'integer',
        'distance'           => 'float',
        'lat'                => 'float',
        'lng'                => 'float',
    ];
}
