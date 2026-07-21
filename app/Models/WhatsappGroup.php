<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'name',
        'default_category',
        'insert_mode',
        'is_active',
    ];
}
