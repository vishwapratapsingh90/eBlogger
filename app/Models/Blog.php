<?php

namespace App\Models;

use Database\Factories\BlogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    /** @use HasFactory<BlogFactory> */
    use HasFactory;
    //
    protected $fillable = [
        'slug',
        'title',
        'content',
        'author_id',
    ];

    public function author() {
        return $this->belongsTo(User::class, 'author_id', 'id');
    }
}
