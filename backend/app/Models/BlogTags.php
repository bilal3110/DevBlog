<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogTags extends Model
{
    use HasFactory;

    protected $table = 'blog_tags';
    public function blog(){
        return $this->belongsTo(Blog::class);
    }

    public function tag(){
        return $this->belongsTo(Tags::class);
    }
}
