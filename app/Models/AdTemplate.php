<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdTemplate extends Model
{
    protected $fillable = [
        'name',
        'description',
        'campaign_type',
        'content_type',
        'template_structure',
        'example_content',
        'is_active',
    ];

    protected $casts = [
        'template_structure' => 'array',
        'example_content' => 'array',
        'is_active' => 'boolean',
    ];
}

