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

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

//<------------------------------ Users Routes ------------------------------>
Route::post('/login', [UserController::class,'login'])->name('user.login');
Route::middleware('auth:sanctum')->post('/logout', [UserController::class, 'logout'])->name('user.logout');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('users', [UserController::class, 'index'])->name('user.show');
    Route::post('users/create', [UserController::class, 'create'])->name('user.create');
    Route::delete('users/delete/{id}', [UserController::class, 'delete'])->name('user.delete');
    Route::patch('users/update/{id}', [UserController::class, 'update'])->name('user.update');
});

//<----------------------------- Post Routes ------------------------>
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/posts/create', [PostController::class, 'create'])->name('post.create');
    Route::delete('/posts/delete/{id}', [PostController::class, 'delete'])->name('post.delete');
    Route::patch('/posts/update/{id}', [PostController::class, 'update'])->name('post.update');
});
Route::get('/posts', [PostController::class, 'index'])->name('post.index');
Route::get('/posts/show/{slug}', [PostController::class, 'show'])->name('post.show');

//<-------------------------- Comments ------------------------------>
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/posts/comments/create/{id}', [CommentController::class, 'store'])->name('create.comment');
    Route::get('/posts/comments/show/{id}', [CommentController::class, 'getComments'])->name('get.comment');
    Route::delete('/posts/comments/delete/{id}', [CommentController::class, 'delete'])->name('delete.comment');
    Route::patch('/posts/comments/edit/{id}', [CommentController::class, 'update'])->name('edit.comment');
});

//<----------------------- Likes ---------------------------------->
Route::middleware('auth:sanctum')->group(function () {
    Route::post('posts/likes/create/{id}', [LikeController::class, 'create'])->name('like.create');
    Route::get('posts/likes/show/{id}', [LikeController::class, 'getLikes'])->name('like.get');
});


//<----------------------------------- Notifications ---------------------------------------->
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notification.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read/all', [NotificationController::class, 'markAllasRead'])->name('notification.read.all');
});

//<-------------------------------- Followers --------------------------->
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/users/follow/{id}', [FollowerController::class, 'toogleFollow'])->name('follow.toogle');
});
Route::get('/users/followers/{id}', [FollowerController::class, 'getFollowers'])->name('getFollowers');
Route::get('/users/following/{id}', [FollowerController::class, 'getFollowing'])->name('getFollowing');

//<--------------------------------- Tags ------------------------------->
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/tags', [TagsController::class, 'index'])->name('tags.view');
    Route::get('/tags/posts/{id}', [TagsController::class, 'tagPosts'])->name('tag.posts');
    Route::get('/tags/trending', [TagsController::class, 'trendingTags'])->name('trendingTags');
});


//<--------------------------------- Share ---------------------------------->
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/post/share/{id}', [ShareController::class, 'sharePost'])->name('share.post');
    Route::get('/post/share/link/{id}', [ShareController::class, 'getLink'])->name('get.Link.post');
});
