<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tags extends Model
{
    use HasFactory;
    protected $fillable = ['name','slug'];

    public function blog(){
        return $this->belongsToMany(Blog::class,'blog_tags','tag_id','blog_id');
    }

    public function blogs(){
        return $this->belongsToMany(Blog::class,'blog_tags','tag_id','blog_id');
    }
}
