<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Tags;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index()
    {
        $posts = Blog::latest()->get();
        return response()->json($posts);
    }

    public function create(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'cover_image' => 'nullable|mimes:jpg,jpeg,png,gif,svg|max:2048',
            'tags' => 'array',
            'tags.*' => 'string|max:255'
        ]);

        $blog = new Blog();
        $blog->title = $request->input('title');
        $blog->content = $request->input('content');
        $blog->slug = Str::slug($request->input('title'));
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
        $blog->syncTags($blog, $request->input('tags', []));

        return response()->json([
            'success' => 'Posted Successfully',
        ], 201);
    }

    public function show($slug)
    {
        $post = Blog::where('slug',$slug);
        if (!$post) {
            return response()->json(['error' => 'Post not found'], 404);
        }
        return response()->json($post);
    }

    public function delete($id)
    {
        $post = Blog::find($id);
        if (!$post) {
            return response()->json(['error' => 'Post not found'], 404);
        }

        if ($post->cover_image) {
            Storage::disk('public')->delete($post->cover_image);
        }

        $post->delete();
        return response()->json(['success' => 'Post deleted successfully'], 200);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'cover_image' => 'nullable|mimes:jpg,jpeg,png,gif,svg|max:2048',
        ]);

        $blog = Blog::find($id);
        if (!$blog) {
            return response()->json(['error' => 'Post not found'], 404);
        }

        $blog->title = $request->input('title');
        $blog->content = $request->input('content');

        $newSlug = Str::slug($request->input('title'));
        if ($blog->slug !== $newSlug) {
            $originalSlug = $newSlug;
            $count = 1;
            while (Blog::where('slug', $newSlug)->where('id', '!=', $id)->exists()) {
                $newSlug = $originalSlug . '-' . $count++;
            }
            $blog->slug = $newSlug;
        }

        if ($request->hasFile('cover_image')) {
            if ($blog->cover_image) {
                Storage::disk('public')->delete($blog->cover_image);
            }

            $file = $request->file('cover_image');
            $path = $file->store('blog-images', 'public');
            $blog->cover_image = $path;
        }

        $blog->save();
        return response()->json(['success' => 'Post updated successfully'], 200);
    }
}
