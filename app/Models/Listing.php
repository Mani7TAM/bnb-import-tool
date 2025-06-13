<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'description',
        'images',
        'coordinates',
        'rating',
        'review_count',
        'room_types',
    ];

    protected $casts = [
        'images' => 'array',
        'coordinates' => 'array',
        'room_types' => 'array',
    ];
}