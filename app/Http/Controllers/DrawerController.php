<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Drawer;
use App\Http\Requests\StoreDrawerRequest;
use App\Http\Requests\UpdateDrawerRequest;
use App\Models\DrawerUser;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Spatie\FlareClient\Api;

class DrawerController extends Controller
{
    public function index()
    {
        try
        {
            $user = auth()->user();
            $drawers = Drawer::all()->map(function ($drawer) use ($user)
            {
                try
                {
                    $membership = $drawer->users()->where('user_id', $user->id)->first();
                    $drawer->role = $membership ? $membership->pivot->role_id : null;
                }
                catch (\Exception $e)
                {
                    $drawer->role = null;
                }
                return $drawer;
            });

            return ApiResponse::success($drawers);
        }
        catch (\Exception $e)
        {
            return ApiResponse::error(message: 'Failed to fetch drawers: ' . $e->getMessage(), status: 500);
        }
    }


    public function store(StoreDrawerRequest $request)
    {
        $validated = $request->validated();

        try
        {
            $drawer = Drawer::create([
                'name' => $validated['name'],
                'description' => $validated['description']
            ]);
            DrawerUser::create([
                'drawer_id' => $drawer->id,
                'user_id' => Auth::user()->id,
                'role_id' => 2
            ]);
            return ApiResponse::success($drawer, status: 201);
        }
        catch (\Exception $e)
        {
            return ApiResponse::error(message: $e->getMessage(), status: 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Drawer $drawer)
    {
        try
        {
            $userId = auth()->id();
            $drawerUser = $drawer->users()->where('user_id', $userId)->first();
            $drawer->role = null;

            if ($drawerUser)
            {
                $roleId = $drawerUser->pivot->role_id;
                if ($roleId)
                {
                    $drawer->role = Role::find($roleId)->name;
                }
            }

            return ApiResponse::success($drawer);
        }
        catch (\Exception $e)
        {
            return ApiResponse::error(message: $e->getMessage(), status: 500);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDrawerRequest $request, Drawer $drawer)
    {
        $validated = $request->validated();

        try
        {
            $drawer->update($validated);
            return ApiResponse::success($drawer);
        }
        catch (\Exception $e)
        {
            return ApiResponse::error(message: $e->getMessage(), status: 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Drawer $drawer)
    {
        Gate::authorize('delete', $drawer);
        try
        {
            $drawer->delete();
            return ApiResponse::success(status: 204);
        }
        catch (\Exception $e)
        {
            return ApiResponse::error(message: $e->getMessage(), status: 500);
        }
    }

    public function posts(Request $request, Drawer $drawer)
    {
        try
        {
            $status = $request->query('status');

            $postsQuery = $drawer->posts()
                ->with([
                    'user:id,username',
                    'drawer:id,name',
                    'post_status:id,name',
                    'images',
                    'reactedByLoggedUser',
                ])
                ->withCount([
                    'comments',
                    'reactions as favors_count' => function (Builder $query)
                    {
                        $query->where('type', 'favor');
                    },
                    'reactions as opposes_count' => function (Builder $query)
                    {
                        $query->where('type', 'oppose');
                    }
                ]);

            if ($status)
            {
                $postsQuery->whereHas('post_status', function ($q) use ($status)
                {
                    $q->where('name', $status);
                });
            }

            $posts = $postsQuery->paginate(5);
            return ApiResponse::success($posts);
        }
        catch (\Exception $e)
        {
            return ApiResponse::error(message: $e->getMessage(), status: 500);
        }
    }

    public function join(Drawer $drawer)
    {
        try
        {
            $userId = auth()->id();

            // Check if the user is already a member of the drawer
            $exists = $drawer->users()->where('user_id', $userId)
                ->where('drawer_id', $drawer->id)
                ->exists();

            if ($exists)
            {
                return ApiResponse::error(409); // Conflict: already a member
            }

            // Create a new DrawerUser entry
            $drawer->users()->attach($userId, ['role_id' => Role::USER]);

            $drawer->role = Role::find(Role::USER)->name;

            return ApiResponse::success($drawer);
        }
        catch (\Exception $e)
        {
            return ApiResponse::error(message: $e->getMessage(), status: 500);
        }
    }

    public function leave(Drawer $drawer)
    {
        try
        {
            $userId = auth()->id();

            $exists = $drawer->users()->where('user_id', $userId)->exists();

            if ($exists)
            {
                $drawer->users()->detach($userId);
                $drawer->role = null;
                return ApiResponse::success($drawer);
            }
            else
            {
                return ApiResponse::error(status: 409);
            }
        }
        catch (\Exception $e)
        {
            return ApiResponse::error(message: $e->getMessage(), status: 500);
        }
    }
}
