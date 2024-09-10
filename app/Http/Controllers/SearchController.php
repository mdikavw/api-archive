<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Drawer;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->query('query');

        $posts = Post::where('title', 'like', "%{$query}%")
            ->orWhere('content', 'like', "%{$query}%")
            ->with('user:id,username')
            ->with('reactedByLoggedUser')
            ->with('images')
            ->withCount(
                [
                    'comments',
                    'reactions as favors_count' => function (Builder $query)
                    {
                        $query->where('type', 'favor');
                    },
                    'reactions as opposes_count' => function (Builder $query)
                    {
                        $query->where('type', 'oppose');
                    }
                ]
            )
            ->get();

        $drawers = Drawer::where('name', 'like', "%{$query}%")
            ->withCount(['users'])
            ->get();

        $users = User::where('username', 'like', "%{$query}%")
            ->get();

        return response()->json([
            'posts' => $posts,
            'drawers' => $drawers,
            'users' => $users
        ]);
    }
}
