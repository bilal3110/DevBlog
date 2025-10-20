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
        $tag = Tags::find($tagID);
        if (!$tag) {
            return response()->json([
                'message' => 'Tag not found'
            ], 404);
        }
        $posts = BlogTags::latest()->where('tag_id', $tagID)->get();
        return response()->json([
            'posts' => $posts
        ], 200);
    }

    public function trendingTags()
    {
        $oneWeekAgo = Carbon::now()->subDays(7);

        $tags = Tags::select('tags.id', 'tags.name', DB::raw('COUNT(blog_tags.blog_id) as post_count'))
            ->join('blog_tags', 'tags.id', '=', 'blog_tags.tag_id')
            ->join('blogs', 'blogs.id', '=', 'blog_tags.blog_id')
            ->where('blogs.created_at', '>=', $oneWeekAgo)
            ->groupBy('tags.id', 'tags.name')
            ->orderByDesc('post_count')
            ->get();

        return response()->json([
            'tags' => $tags
        ], 200);
    }
}
