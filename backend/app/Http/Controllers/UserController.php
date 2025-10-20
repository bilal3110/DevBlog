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
            'name' => 'required',
            'email' => 'required|unique:users,email',
            'password' => 'required|min:8',
            'bio' => 'nullable',
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
            
        }

        $user->save();
        return response()->json(['message' => 'User created successfully'], 201);
    }


    public function index()
    {
        $users = User::all();
        return response()->json($users);
    }

    public function delete($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User Not Found'
            ], 404);
        } else {
            $user->delete();
            return response()->json(['message' => 'User deleted successfully'], 200);
        }
    }

    public function update(Request $request, $id)
    {
        // $request->validate([
        //     'name' => 'required',
        //     'email' => 'required|unique:users,email,' . $id,
        //     'password' => 'nullable|min:8',
        //     'bio' => 'nullable',
        //     'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        //     'github' => 'nullable|url',
        //     'portfolio' => 'nullable|url',
        // ]);

        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User Not Found'], 404);
        }

        $user->name = $request->input('name');
        $user->email = $request->input('email');
        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
        }
        $user->bio = $request->input('bio');
        $user->github = $request->input('github');
        $user->portfolio = $request->input('portfolio');

        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->save();
        return response()->json(['message' => 'User updated successfully'], 200);
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
