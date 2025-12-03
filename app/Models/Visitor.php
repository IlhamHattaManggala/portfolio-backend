<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'user_agent',
        'referer',
        'path',
        'country',
        'city',
        'device',
        'browser',
        'platform',
        'is_unique',
        'visited_at',
    ];

    protected $casts = [
        'is_unique' => 'boolean',
        'visited_at' => 'datetime',
    ];
}
