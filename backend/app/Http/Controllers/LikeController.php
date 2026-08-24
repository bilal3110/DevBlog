<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Likes;
use Illuminate\Http\Request;
use App\Helpers\Notify;


class LikeController extends Controller
{
    public function create(Request $request, $blogId)
    {
        $blog = Blog::find($blogId);
        if (!$blog) {
            return response()->json(['error' => 'Blog not found'], 404);
        }

        $userId = auth()->user()->id;

        $existingLike = Likes::where('blog_id', $blogId)
            ->where('user_id', $userId)
            ->first();

        if ($existingLike) {
            $existingLike->delete();
            return response()->json([
                'message' => 'Like removed',
                'liked' => false,
                'total_likes' => Likes::where('blog_id', $blogId)->count()
            ]);
        }

        $like = Likes::create([
            'blog_id' => $blogId,
            'user_id' => $userId,
        ]);

        if ($userId !== $blog->user_id) {
            Notify::send(
                $blog->user_id,
                'like',
                auth()->user()->name . ' liked your post: "' . $blog->title . '"'
            );
        }

        return response()->json([
            'message' => 'Like added',
            'liked' => true,
            'total_likes' => Likes::where('blog_id', $blogId)->count(),
            'like' => [
                'id' => $like->id,
                'blog_id' => $like->blog_id,
                'user' => [
                    'id' => $like->user_id,
                    'name' => $like->user->name ?? 'Unknown',
                ]
            ]
        ]);
    }

    public function getLikes($blogId)
    {
        $blog = Blog::find($blogId);
        if (!$blog) {
            return response()->json(['error' => 'Blog not found'], 404);
        }

        $likes = Likes::where('blog_id', $blogId)->with('user')->get();

        $likeData = $likes->map(function ($like) {
            return [
                'id' => $like->id,
                'blog_id' => $like->blog_id,
                'user' => [
                    'id' => $like->user_id,
                    'name' => $like->user->name ?? 'Unknown',
                ]
            ];
        });

        return response()->json([
            'likes' => $likeData,
            'total' => $likes->count()
        ]);
    }

}
