<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tags;
use Illuminate\Support\Str;

class Blog extends Model
{
    use HasFactory;
    protected $table = "blogs";

    protected $fillable = ['title', 'content', 'slug', 'cover_image', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tags::class, 'blog_tags', 'blog_id', 'tag_id');
    }

    public function comments()
    {
        return $this->hasMany(Comments::class, 'blog_id');
    }

    public function like()
    {
        return $this->hasMany(Likes::class, 'blog_id');
    }

    public function share()
    {
        return $this->hasMany(Share::class, 'blog_id');
    }

    public function syncTags(Blog $blog, array $tagNames)
    {
        $tagIds = [];

        foreach ($tagNames as $tagName) {
            $tagName = trim($tagName);
            if (empty($tagName)) {
                continue;
            }

            $existing = Tags::where('name', $tagName)->first();
            if ($existing) {
                $tagIds[] = $existing->id;
                continue;
            }

            $baseSlug = Str::slug($tagName) ?: 'tag';
            $slug = $baseSlug;
            $counter = 1;

            while (Tags::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }

            $tag = Tags::create([
                'name' => $tagName,
                'slug' => $slug
            ]);

            $tagIds[] = $tag->id;
        }

        $blog->tags()->sync($tagIds);
    }

}
