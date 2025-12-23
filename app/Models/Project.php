<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'descriptions',
        'tipe',
        'library',
        'image',
        'link',
        'order',
        'is_active',
    ];

    protected $casts = [
        'library' => 'array',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    public function technologies()
    {
        return $this->belongsToMany(Technology::class, 'project_technology');
    }
}
