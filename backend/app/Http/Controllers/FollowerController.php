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
        if ($user->isFollowing($followed)) {
            $user->unfollow($followed);
            return response()->json(['message' => 'Unfollowed'], 200);
        } else {
            $user->follow($followed);
            Notify::send($user->id, 'follow', auth()->user()->name . ' started following you');
            return response()->json(['message' => 'Followed successfully'], 200);

        }
    }

    public function getFollowers($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        return response()->json([
            'followers' => $user->followers()->get(),
            'total' => $user->followers()->count()
        ]);
    }

    public function getFollowing($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        return response()->json([
            'following' => $user->following()->get(),
            'total' => $user->following()->count()
        ]);
    }
}
