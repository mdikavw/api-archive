<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\StorePostRequest;
use App\Models\Post;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Requests\UpdatePostStatusRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index()
    {
        try
        {
            $posts = Post::latest()
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
                ])
                ->paginate(5);

            return ApiResponse::success($posts);
        }
        catch (\Exception $e)
        {
            return ApiResponse::error(message: $e->getMessage(), status: 500);
        }
    }


    public function store(StorePostRequest $request)
    {
        try
        {
            $validated = $request->validated();

            $slug = Str::slug($validated['title']);
            $originalSlug = $slug;
            $counter = 1;

            while (Post::where('slug', $slug)->exists())
            {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            // Create the post
            $post = Post::create([
                'title' => $validated['title'],
                'content' => $validated['content'],
                'slug' => $slug,
                'user_id' => auth()->user()->id,
                'type' => $request->input('type'),
                'drawer_id' => $request->input('drawer_id') == 'null' ? null : $request->input('drawer_id'),
            ]);

            if ($request->hasFile('images'))
            {
                foreach ($request->file('images') as $image)
                {
                    $imagePath = $image->store('images', 'public');
                    $post->images()->create(['image_path' => $imagePath]);
                }
            }

            return ApiResponse::success($post, status: 201);
        }
        catch (\Exception $e)
        {
            return ApiResponse::error(message: $e->getMessage(), status: 500);
        }
    }


    public function show(Post $post)
    {
        try
        {
            $post->load([
                'user:id,username',
                'drawer:id,name',
                'images',
                'reactedByLoggedUser',
            ])->loadCount([
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

            return ApiResponse::success($post);
        }
        catch (\Exception $e)
        {
            return ApiResponse::error(500);
        }
    }


    public function update(UpdatePostRequest $request, Post $post)
    {
        try
        {
            $validated = $request->validated();

            $post->update($validated);

            return ApiResponse::success([
                'message' => 'Post updated successfully',
                'post' => $post
            ]);
        }
        catch (\Exception $e)
        {
            return ApiResponse::error(500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        try
        {
            // Authorize the action
            Gate::authorize('delete', $post);

            $imagePath = storage_path('app/public/' . $post->image_path);

            // Delete the image if it exists
            if (File::exists($imagePath))
            {
                File::delete($imagePath);
            }

            // Delete the post
            $post->delete();

            return ApiResponse::success();
        }
        catch (\Exception $e)
        {
            return ApiResponse::error(500);
        }
    }


    public function updateStatus(UpdatePostStatusRequest $request, Post $post)
    {
        try
        {
            $validated = $request->validated();

            switch ($validated['status'])
            {
                case 'approve':
                    $post->update(['post_status_id' => 1]);
                    break;
                case 'reject':
                    $post->update(['post_status_id' => 2]);
                    break;
                default:
                    return ApiResponse::error(null, 422);
            }

            return ApiResponse::success();
        }
        catch (\Exception $e)
        {
            return ApiResponse::error(null, 500);
        }
    }
}
