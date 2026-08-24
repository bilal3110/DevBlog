<?php

namespace App\Http\Controllers;

use App\Models\User;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'bio' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'github' => 'nullable|url',
            'portfolio' => 'nullable|url',
        ]);

        $user = new User();
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->password = Hash::make($request->input('password'));
        $user->bio = $request->input('bio');
        $user->github = $request->input('github');
        $user->portfolio = $request->input('portfolio');

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->save();
        return response()->json(['message' => 'User created successfully', 'user' => $user], 201);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->loadCount(['followers', 'following', 'blog']);
        }
        return response()->json($user);
    }

    public function show($id)
    {
        $user = User::withCount(['followers', 'following', 'blog'])->find($id);
        if (!$user) {
            return response()->json(['message' => 'User Not Found'], 404);
        }

        $isFollowing = false;
        if (auth('sanctum')->check()) {
            $isFollowing = auth('sanctum')->user()->isFollowing($user);
        }

        $userArray = $user->toArray();
        $userArray['is_following'] = $isFollowing;

        return response()->json($userArray);
    }

    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Exclude current user from suggestions if authenticated
        if (auth('sanctum')->check() && $request->has('exclude_me')) {
            $query->where('id', '!=', auth('sanctum')->id());
        }

        if ($request->has('limit')) {
            $query->limit((int)$request->limit);
        }

        $users = $query->latest()->get();

        if (auth('sanctum')->check()) {
            $authUser = auth('sanctum')->user();
            $users->transform(function ($user) use ($authUser) {
                $user->is_following = $authUser->isFollowing($user);
                return $user;
            });
        }

        return response()->json($users);
    }

    public function delete($id)
    {
        if (auth()->id() != $id) {
            return response()->json(['message' => 'Unauthorized to delete this user'], 403);
        }

        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User Not Found'
            ], 404);
        }

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();
        return response()->json(['message' => 'User deleted successfully'], 200);
    }

    public function update(Request $request, $id)
    {
        if (auth()->id() != $id) {
            return response()->json(['message' => 'Unauthorized to update this user'], 403);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:8',
            'bio' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'github' => 'nullable|url',
            'portfolio' => 'nullable|url',
        ]);

        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User Not Found'], 404);
        }

        if ($request->filled('name')) {
            $user->name = $request->input('name');
        }
        if ($request->filled('email')) {
            $user->email = $request->input('email');
        }
        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
        }
        if ($request->has('bio')) {
            $user->bio = $request->input('bio');
        }
        if ($request->has('github')) {
            $user->github = $request->input('github');
        }
        if ($request->has('portfolio')) {
            $user->portfolio = $request->input('portfolio');
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->save();
        return response()->json(['message' => 'User updated successfully', 'user' => $user], 200);
    }

    public function login(Request $request){
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        $user = User::where('email',$request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json(['token' => $token, 'user' => $user]);
    }

    public function logout(Request $request){
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully'], 200);
    }

}
