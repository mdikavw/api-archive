<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostImage extends Model
{
    use HasFactory;
    protected $fillable = ['image_path'];

    public function getImagePathAttribute($value)
    {
        return asset('storage/' . $value);
    }
}
