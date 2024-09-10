<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use App\Http\Requests\UpdateCommentRequest;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Post $post)
    {
        try
        {
            $comments = $post->comments()->where('parent_id', null)->get()->load('user', 'reactedByLoggedUser')->loadCount([
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
            return ApiResponse::success($comments);
        }
        catch (\Exception $e)
        {
            return ApiResponse::error(message: $e->getMessage(), status: 500);
        }
    }

    public function replies(Comment $comment)
    {
        $replies = $comment->comments->load('user', 'post', 'comments', 'reactedByLoggedUser')->loadCount([
            'comments',
            'reactions as favors_count' => function (Builder $query)
            {
                $query->where('type', 'favor');
            },
            'reactions as opposes_count' => function (Builder $query)
            {
                $query->where('type', 'oppose');
            }
        ])->toArray();
        return response()->json($replies);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCommentRequest $request)
    {
        $userId = auth()->id();

        $validated = $request->validated();

        $comment = Comment::create([
            'content' => $validated['content'],
            'user_id' => $userId,
            'post_id' => $validated['post_id'],
            'parent_id' => $validated['parent_id']
        ]);
        $comment->load('user', 'reactedByLoggedUser')->loadCount([
            'comments',
            'reactions as favors_count' => function (Builder $query)
            {
                $query->where('type', 'favor');
            },
            'reactions as opposes_count' => function (Builder $query)
            {
                $query->where('type', 'oppose');
            }
        ])->toArray();
        return response()->json($comment, 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCommentRequest $request, Comment $comment)
    {
        $validated = $request->validated();
        $update = $comment->update([
            'content' => $validated['content']
        ]);

        if ($update)
        {
            return response()->json($comment);
        }
        else
        {
            return response()->json(null, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment)
    {
        Gate::authorize('delete', $comment);
        try
        {
            $comment->delete();

            return response()->json(null, 204);
        }
        catch (\Exception $e)
        {
            return response()->json(['error' => 'An error occurred while deleting the comment.', 'message' => $e->getMessage()], 500);
        }
    }
}
