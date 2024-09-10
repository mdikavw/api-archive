<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\DrawerController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ReactionController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UserController;
use App\Models\Drawer;
use App\Models\Reaction;
use GuzzleHttp\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function ()
{
    Route::get('/user', function (Request $request)
    {
        return $request->user();
    });

    Route::get('/user/drawers', [UserController::class, 'drawers']);

    Route::get('/posts', [PostController::class, 'index'])->withoutMiddleware('auth:sanctum');
    Route::get('/posts/{post:slug}', [PostController::class, 'show']);
    Route::post('/posts', [PostController::class, 'store']);
    Route::patch('/posts/{post:slug}', [PostController::class, 'update']);
    Route::delete('/posts/{post:slug}', [PostController::class, 'destroy']);

    Route::prefix('posts/{post:slug}')->group(function ()
    {
        Route::patch('/status', [PostController::class, 'updateStatus']);
    });

    Route::post('/reactions', [ReactionController::class, 'store']);
    Route::patch('/reactions/{reaction}', [ReactionController::class, 'update']);
    Route::delete('/reactions/{reaction}', [ReactionController::class, 'destroy']);

    Route::get('/posts/{post:slug}/comments', [CommentController::class, 'index'])->withoutMiddleware('auth:sanctum');
    Route::get('/comments/{comment}/replies', [CommentController::class, 'replies'])->withoutMiddleware('auth:sanctum');
    Route::post('/comments', [CommentController::class, 'store']);
    Route::patch('/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

    Route::post('/drawers', [DrawerController::class, 'store']);
    Route::get('/drawers', [DrawerController::class, 'index'])->withoutMiddleware('auth:sanctum');
    Route::get('/drawers/{drawer:name}', [DrawerController::class, 'show']);
    Route::get('/drawers/{drawer:name}/posts', [DrawerController::class, 'posts']);
    Route::patch('/drawers/{drawer:name}', [DrawerController::class, 'update']);
    Route::delete('/drawers/{drawer:name}', [DrawerController::class, 'destroy']);
    Route::post('/drawers/{drawer:name}/join', [DrawerController::class, 'join']);
    Route::delete('/drawers/{drawer:name}/leave', [DrawerController::class, 'leave']);

    Route::get('/search', [SearchController::class, 'search']);

    Route::get('/users/{user:username}/posts', [UserController::class, 'posts'])->withoutMiddleware('auth:sanctum');
    Route::post('/logout', [LoginController::class, 'logout']);
});

Route::post('/register', [RegisterController::class, 'store']);
Route::post('/login', [LoginController::class, 'authenticate']);
