<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\Notify;

class FollowerController extends Controller
{
    public function toogleFollow(Request $request, $followedId)
    {
        $user = auth()->user();
        $followed = User::find($followedId);
        if (!$followed) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if ($user->id == $followed->id) {
            return response()->json(['error' => 'You cannot follow yourself'], 400);
        }

        if ($user->isFollowing($followed)) {
            $user->unfollow($followed);
            return response()->json([
                'message' => 'Unfollowed',
                'following' => false,
                'total_followers' => $followed->followers()->count()
            ], 200);
        } else {
            $user->follow($followed);
            Notify::send($followed->id, 'follow', auth()->user()->name . ' started following you');
            return response()->json([
                'message' => 'Followed successfully',
                'following' => true,
                'total_followers' => $followed->followers()->count()
            ], 200);
        }
    }

    public function getFollowers($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        $followers = $user->followers()->with('follower:id,name,email,avatar,bio')->get()->pluck('follower');
        return response()->json([
            'followers' => $followers,
            'total' => $followers->count()
        ]);
    }

    public function getFollowing($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        $following = $user->following()->with('followed:id,name,email,avatar,bio')->get()->pluck('followed');
        return response()->json([
            'following' => $following,
            'total' => $following->count()
        ]);
    }
}
