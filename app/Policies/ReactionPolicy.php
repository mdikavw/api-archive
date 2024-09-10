<?php

namespace App\Policies;

use App\Models\Reaction;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReactionPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Reaction $reaction): bool
    {
        return $user->id === $reaction->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Reaction $reaction): bool
    {
        return $user->id === $reaction->user_id;
    }
}
