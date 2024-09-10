<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Drawer extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'drawer_user')->withTimestamps()->withPivot('role_id');
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
