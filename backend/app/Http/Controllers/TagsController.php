<?php

namespace App\Http\Controllers;

use App\Models\BlogTags;
use App\Models\Tags;
use Illuminate\Http\Request;
use Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TagsController extends Controller
{
    public function index()
    {
        $tags = Tags::all();
        return response()->json([
            'tags' => $tags
        ], 200);
    }

    public function tagPosts($tagID)
    {
        $tag = is_numeric($tagID) ? Tags::find($tagID) : Tags::where('slug', $tagID)->orWhere('name', $tagID)->first();
        if (!$tag) {
            return response()->json([
                'message' => 'Tag not found'
            ], 404);
        }

        $posts = $tag->blogs()
            ->with(['user:id,name,email,avatar', 'tags:id,name,slug'])
            ->withCount(['comments', 'like'])
            ->latest()
            ->get();

        if (auth('sanctum')->check()) {
            $userId = auth('sanctum')->id();
            $posts->transform(function ($post) use ($userId) {
                $post->is_liked = $post->like()->where('user_id', $userId)->exists();
                return $post;
            });
        }

        return response()->json([
            'tag' => $tag,
            'posts' => $posts
        ], 200);
    }

    public function trendingTags()
    {
        $oneWeekAgo = Carbon::now()->subDays(7);

        $tags = Tags::select('tags.id', 'tags.name', 'tags.slug', DB::raw('COUNT(blog_tags.blog_id) as post_count'))
            ->join('blog_tags', 'tags.id', '=', 'blog_tags.tag_id')
            ->join('blogs', 'blogs.id', '=', 'blog_tags.blog_id')
            ->where('blogs.created_at', '>=', $oneWeekAgo)
            ->groupBy('tags.id', 'tags.name', 'tags.slug')
            ->orderByDesc('post_count')
            ->limit(10)
            ->get();

        // Fallback to top tags overall if no activity in last 7 days
        if ($tags->isEmpty()) {
            $tags = Tags::select('tags.id', 'tags.name', 'tags.slug', DB::raw('COUNT(blog_tags.blog_id) as post_count'))
                ->leftJoin('blog_tags', 'tags.id', '=', 'blog_tags.tag_id')
                ->groupBy('tags.id', 'tags.name', 'tags.slug')
                ->orderByDesc('post_count')
                ->limit(10)
                ->get();
        }

        return response()->json([
            'tags' => $tags
        ], 200);
    }
}
