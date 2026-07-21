<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthorFavorite extends Model
{
    protected $table = 'author_favorites';

    protected $fillable = [
        'user_id',
        'author_id',
        'author_type',
        'source_name',
    ];
}
