<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function posts(User $user)
    {
        $posts = $user->posts()
            ->with([
                'user:id,username',
                'drawer:id,name',
                'images',
                'post_status:id,name',
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
            ])->paginate();

        return ApiResponse::success($posts);
    }
    public function drawers(Request $request)
    {
        try
        {
            return ApiResponse::success($request->user()->drawers()->get());
        }
        catch (Exception $e)
        {
            return ApiResponse::error(message: $e->getMessage());
        }
    }

    public function uploadProfilePicture(Request $request, User $user)
    {
        try
        {
            $request->validate([
                'profile_picture' => 'required|image|mimes:jpg,jpeg,png|max:2048'
            ]);

            if ($request->hasFile('profile_picture'))
            {
                if ($user->profile_picture_path)
                {
                    Storage::disk('public')->delete($user->profile_picture_path);
                }

                $path = $request->file('profile_picture')->store('profile_picture', 'public');
                $user->profile_picture_path = $path;
                $user->save();
                return ApiResponse::success();
            }
            return ApiResponse::error(message: 'No file uploaded', status: 400);
        }
        catch (Exception $e)
        {
            return ApiResponse::error(status: 500);
        }
    }
}
