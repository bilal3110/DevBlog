<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Tags;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = Blog::with(['user:id,name,email,avatar', 'tags:id,name,slug'])
            ->withCount(['comments', 'like']);

        // Filter by search query
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Filter by user ID
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by tag slug or name
        if ($request->filled('tag')) {
            $tag = $request->tag;
            $query->whereHas('tags', function ($q) use ($tag) {
                $q->where('slug', $tag)->orWhere('name', $tag);
            });
        }

        // Filter by tab: feed (following only), trending, or forYou
        if ($request->filled('tab')) {
            $tab = $request->tab;
            if ($tab === 'feed' && auth('sanctum')->check()) {
                $followingIds = auth('sanctum')->user()->following()->pluck('followed_id');
                $query->whereIn('user_id', $followingIds);
            } elseif ($tab === 'trending') {
                $query->orderByDesc('like_count')->orderByDesc('comments_count');
            }
        }

        $posts = $query->latest()->get();

        // Attach is_liked for authenticated user
        if (auth('sanctum')->check()) {
            $userId = auth('sanctum')->id();
            $posts->transform(function ($post) use ($userId) {
                $post->is_liked = $post->like()->where('user_id', $userId)->exists();
                return $post;
            });
        } else {
            $posts->transform(function ($post) {
                $post->is_liked = false;
                return $post;
            });
        }

        return response()->json($posts);
    }

    public function create(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'cover_image' => 'nullable|mimes:jpg,jpeg,png,gif,svg,webp|max:5120',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:255'
        ]);

        $blog = new Blog();
        $blog->title = $request->input('title');
        $blog->content = $request->input('content');
        $blog->slug = Str::slug($request->input('title')) ?: 'post-' . uniqid();
        $blog->user_id = auth()->id();

        $originalSlug = $blog->slug;
        $count = 1;
        while (Blog::where('slug', $blog->slug)->exists()) {
            $blog->slug = $originalSlug . '-' . $count++;
        }

        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $path = $file->store('blog-images', 'public');
            $blog->cover_image = $path;
        }

        $blog->save();

        if ($request->has('tags')) {
            $tags = is_array($request->input('tags')) ? $request->input('tags') : json_decode($request->input('tags'), true) ?? [];
            $blog->syncTags($blog, $tags);
        }

        $blog->load(['user:id,name,email,avatar', 'tags:id,name,slug']);

        return response()->json([
            'success' => 'Posted Successfully',
            'post' => $blog,
            'slug' => $blog->slug,
        ], 201);
    }

    public function show($slug)
    {
        $post = Blog::with([
            'user:id,name,email,avatar,bio,github,portfolio',
            'tags:id,name,slug',
            'comments.user:id,name,email,avatar',
        ])
        ->withCount(['comments', 'like'])
        ->where('slug', $slug)
        ->first();

        if (!$post) {
            return response()->json(['error' => 'Post not found'], 404);
        }

        $isLiked = false;
        $isAuthor = false;
        if (auth('sanctum')->check()) {
            $userId = auth('sanctum')->id();
            $isLiked = $post->like()->where('user_id', $userId)->exists();
            $isAuthor = ($post->user_id === $userId);
        }

        $postArray = $post->toArray();
        $postArray['is_liked'] = $isLiked;
        $postArray['is_author'] = $isAuthor;

        return response()->json($postArray);
    }

    public function delete($id)
    {
        $post = Blog::find($id);
        if (!$post) {
            return response()->json(['error' => 'Post not found'], 404);
        }

        if ($post->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized to delete this post'], 403);
        }

        if ($post->cover_image && Storage::disk('public')->exists($post->cover_image)) {
            Storage::disk('public')->delete($post->cover_image);
        }

        $post->tags()->detach();
        $post->comments()->delete();
        $post->like()->delete();
        $post->share()->delete();
        $post->delete();

        return response()->json(['success' => 'Post deleted successfully'], 200);
    }

    public function update(Request $request, $id)
    {
        $blog = Blog::find($id);
        if (!$blog) {
            return response()->json(['error' => 'Post not found'], 404);
        }

        if ($blog->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized to update this post'], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'cover_image' => 'nullable|mimes:jpg,jpeg,png,gif,svg,webp|max:5120',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:255'
        ]);

        $blog->title = $request->input('title');
        $blog->content = $request->input('content');

        $newSlug = Str::slug($request->input('title')) ?: 'post-' . $id;
        if ($blog->slug !== $newSlug) {
            $originalSlug = $newSlug;
            $count = 1;
            while (Blog::where('slug', $newSlug)->where('id', '!=', $id)->exists()) {
                $newSlug = $originalSlug . '-' . $count++;
            }
            $blog->slug = $newSlug;
        }

        if ($request->hasFile('cover_image')) {
            if ($blog->cover_image && Storage::disk('public')->exists($blog->cover_image)) {
                Storage::disk('public')->delete($blog->cover_image);
            }

            $file = $request->file('cover_image');
            $path = $file->store('blog-images', 'public');
            $blog->cover_image = $path;
        }

        $blog->save();

        if ($request->has('tags')) {
            $tags = is_array($request->input('tags')) ? $request->input('tags') : json_decode($request->input('tags'), true) ?? [];
            $blog->syncTags($blog, $tags);
        }

        return response()->json(['success' => 'Post updated successfully', 'slug' => $blog->slug, 'post' => $blog], 200);
    }
}
