<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Share extends Model
{
    use HasFactory;
    protected $table = 'share';
    protected $fillable = ['blog_id','platform'];

    public function blog()
    {
        return $this->belongsTo(Blog::class,'blog_id');
    }
}
