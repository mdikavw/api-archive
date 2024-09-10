<?php

namespace App\Policies;

use App\Models\Drawer;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DrawerPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Drawer $drawer): bool
    {
        return $user->isModeratorOfDrawer($drawer->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Drawer $drawer): bool
    {
        return $user->isModeratorOfDrawer($drawer->id);
    }
}
