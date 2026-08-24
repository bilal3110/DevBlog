<?php

namespace App\Http\Controllers;

use App\Helpers\Notify;
use App\Models\Blog;
use App\Models\Comments;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CommentController extends Controller
{
    public function store(Request $request, int $blogId): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|min:1|max:1000',
        ]);

        $blog = Blog::find($blogId);
        if (!$blog) {
            return response()->json([
                'error' => 'Blog post not found'
            ], 404);
        }

        $comment = Comments::create([
            'content' => $request->input('content'),
            'blog_id' => $blogId,
            'user_id' => auth()->id(),
        ]);

        $comment->load('user:id,name,email,avatar');

        if(auth()->user()->id != $blog->user_id){
            Notify::send(
                $blog->user_id,
                'comment',
                auth()->user()->name . ' commented on your post: "'. $blog->title . '"',
            );
        }

        return response()->json([
            'success' => 'Comment added successfully',
            'comment' => [
                'id' => $comment->id,
                'content' => $comment->content,
                'created_at' => $comment->created_at->format('Y-m-d H:i:s'),
                'user' => [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                    'avatar' => $comment->user->avatar,
                ]
            ]
        ], 201);
    }

    public function getComments(int $blogId): JsonResponse
    {
        $blog = Blog::find($blogId);
        if (!$blog) {
            return response()->json([
                'error' => 'Blog post not found'
            ], 404);
        }

        $comments = Comments::where('blog_id', $blogId)
            ->with('user:id,name,email,avatar')
            ->latest()
            ->get()
            ->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'created_at' => $comment->created_at->format('Y-m-d H:i:s'),
                    'user' => [
                        'id' => $comment->user->id,
                        'name' => $comment->user->name,
                        'avatar' => $comment->user->avatar,
                    ]
                ];
            });

        return response()->json([
            'comments' => $comments,
            'total' => $comments->count()
        ]);
    }

    public function update(Request $request, int $commentId): JsonResponse
{
    $request->validate([
        'content' => 'required|string|min:1|max:1000',
    ]);

    $comment = Comments::find($commentId);

    if (!$comment) {
        return response()->json([
            'error' => 'Comment not found'
        ], 404);
    }

    if ($comment->user_id !== auth()->id()) {
        return response()->json([
            'error' => 'Unauthorized to update this comment'
        ], 403);
    }

    $comment->content = $request->input('content');
    $comment->save();

    return response()->json([
        'success' => 'Comment updated successfully',
        'comment' => [
            'id' => $comment->id,
            'content' => $comment->content,
            'updated_at' => $comment->updated_at->format('Y-m-d H:i:s'),
        ]
    ]);
}


    public function delete(int $commentId): JsonResponse
    {
        $comment = Comments::find($commentId);

        if (!$comment) {
            return response()->json([
                'error' => 'Comment not found'
            ], 404);
        }

        if ($comment->user_id !== auth()->id() && $comment->blog->user_id !== auth()->id()) {
            return response()->json([
                'error' => 'Unauthorized to delete this comment'
            ], 403);
        }

        $comment->delete();

        return response()->json([
            'success' => 'Comment deleted successfully'
        ]);
    }
}
