<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Share;
use Illuminate\Http\Request;

class ShareController extends Controller
{
    public function sharePost(Request $request,$blogId)
    {
        $request->validate([
            'platform' => 'required|in:facebook,whatsapp,x/twitter,linkedin,instagram',
        ]);
        $blog = Blog::find($blogId);
        if(!$blog){
            return response()->json(['error' => 'Blog not found'], 404);
        }
        $share = new Share();
        $share->blog_id = $blogId;
        $share->platform = $request->platform;
        $share->save();
        return response()->json([
            'message' => 'Post shared successfully',
            'data' => $share
        ]);
    }

    public function getLink($id){
        $post = Blog::find($id);
        if(!$post){
            return response()->json(['error' => 'Blog not found'], 404);
        }
        $url = route('post.show',[
            'slug' => $post->slug,
        ]);
        return response()->json([
            'link' => $url
        ],200);
    }
}
