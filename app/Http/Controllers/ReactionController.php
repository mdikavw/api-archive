<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReactionRequest;
use App\Http\Requests\UpdateReactionRequest;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Reaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

use function Laravel\Prompts\alert;

class ReactionController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReactionRequest $request)
    {
        $userId = auth()->id();
        $validated = $request->validated();
        $reactableTypeMap = [
            'post' => Post::class,
            'comment' => Comment::class,
        ];

        $reactableClass = $reactableTypeMap[$request->reactable_type];
        $reactable = $reactableClass::find($request->reactable_id);

        if (!$reactable)
        {
            return response()->json(['message' => 'Reactable entity not found'], 404);
        }

        $reaction = Reaction::create([
            'user_id' => $userId,
            'reactable_id' => $validated['reactable_id'],
            'reactable_type' => $reactableTypeMap[$validated['reactable_type']],
            'type' => $validated['type']
        ]);

        $reaction->reactable_type = $validated['reactable_type'];
        return response()->json(
            $reaction,
            201
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateReactionRequest $request, Reaction $reaction)
    {
        $validated = $request->validated();

        $update = $reaction->update([
            'type' => $validated['type']
        ]);

        $reactableTypeMap = [
            Post::class => 'post',
            Comment::class => 'comment'
        ];

        if ($update)
        {
            $reaction->reactable_type = $reactableTypeMap[$reaction->reactable_type];
            return response()->json($reaction);
        }
        else
        {
            return response()->json(null, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reaction $reaction)
    {
        Gate::authorize('delete', $reaction);
        $delete = $reaction->delete();

        if ($delete)
        {
            return response()->json(null);
        }
        else
        {
            return response()->json(null, 500);
        }
    }
}
