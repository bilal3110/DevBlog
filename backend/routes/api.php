<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\FollowerController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\TagsController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


//<------------------------------ Users Routes ------------------------------>
Route::post('users/create', [UserController::class, 'create'])->name('user.create');
Route::post('/login', [UserController::class, 'login'])->name('user.login');
Route::get('/users', [UserController::class, 'index'])->name('user.index');
Route::get('/users/{id}', [UserController::class, 'show'])->name('user.show');
Route::get('/users/followers/{id}', [FollowerController::class, 'getFollowers'])->name('getFollowers');
Route::get('/users/following/{id}', [FollowerController::class, 'getFollowing'])->name('getFollowing');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [UserController::class, 'me'])->name('user.me');
    Route::post('/logout', [UserController::class, 'logout'])->name('user.logout');
    Route::delete('users/delete/{id}', [UserController::class, 'delete'])->name('user.delete');
    Route::match(['patch', 'post'], 'users/update/{id}', [UserController::class, 'update'])->name('user.update');
    Route::post('/users/follow/{id}', [FollowerController::class, 'toogleFollow'])->name('follow.toogle');
});

//<----------------------------- Post Routes ------------------------>
Route::get('/posts', [PostController::class, 'index'])->name('post.index');
Route::get('/posts/show/{slug}', [PostController::class, 'show'])->name('post.show');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/posts/create', [PostController::class, 'create'])->name('post.create');
    Route::delete('/posts/delete/{id}', [PostController::class, 'delete'])->name('post.delete');
    Route::match(['patch', 'post'], '/posts/update/{id}', [PostController::class, 'update'])->name('post.update');
});

//<-------------------------- Comments ------------------------------>
Route::get('/posts/comments/show/{id}', [CommentController::class, 'getComments'])->name('get.comment');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/posts/comments/create/{id}', [CommentController::class, 'store'])->name('create.comment');
    Route::delete('/posts/comments/delete/{id}', [CommentController::class, 'delete'])->name('delete.comment');
    Route::patch('/posts/comments/edit/{id}', [CommentController::class, 'update'])->name('edit.comment');
});

//<----------------------- Likes ---------------------------------->
Route::get('posts/likes/show/{id}', [LikeController::class, 'getLikes'])->name('like.get');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('posts/likes/create/{id}', [LikeController::class, 'create'])->name('like.create');
});

//<----------------------------------- Notifications ---------------------------------------->
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notification.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read/all', [NotificationController::class, 'markAllasRead'])->name('notification.read.all');
});

//<--------------------------------- Tags ------------------------------->
Route::get('/tags', [TagsController::class, 'index'])->name('tags.view');
Route::get('/tags/posts/{id}', [TagsController::class, 'tagPosts'])->name('tag.posts');
Route::get('/tags/trending', [TagsController::class, 'trendingTags'])->name('trendingTags');

//<--------------------------------- Share ---------------------------------->
Route::get('/post/share/link/{id}', [ShareController::class, 'getLink'])->name('get.Link.post');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/post/share/{id}', [ShareController::class, 'sharePost'])->name('share.post');
});
