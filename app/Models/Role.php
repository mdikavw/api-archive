<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    const ADMIN = 1;
    const MODERATOR = 2;
    const USER = 3;

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function drawers()
    {
        return $this->belongsToMany(Drawer::class);
    }
}
