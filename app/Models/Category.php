<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id',
        'name',
        'slug',
        'parent_id',
    ];


    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function roles()
    {
        return $this->hasMany(CategoryRole::class);
    }

    public function fields()
    {
        return $this->hasMany(CategoryField::class, 'category_id');
    }
}
