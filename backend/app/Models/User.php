<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function blog()
    {
        return $this->hasMany(Blog::class);
    }

    public function comments()
    {
        return $this->hasMany(Comments::class);
    }

    public function likes()
    {
        return $this->hasMany(Likes::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notifications::class);
    }

    public function following()
    {
        return $this->hasMany(Follows::class, 'follower_id');
    }

    public function followers()
    {
        return $this->hasMany(Follows::class, 'followed_id');
    }

    public function isFollowing(User $user)
    {
        return Follows::where('follower_id', $this->id)
            ->where('followed_id', $user->id)
            ->exists();
    }

    public function follow(User $user)
    {
        return Follows::create([
            'follower_id' => $this->id,
            'followed_id' => $user->id
        ]);
    }

    public function unfollow(User $user)
    {
        return Follows::where('follower_id', $this->id)
            ->where('followed_id', $user->id)
            ->delete();
    }

}
