<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CategoryField extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'external_id',
        'attribute',
        'name',
        'value_type',
        'filter_type',
        'is_required',
        'min_value',
        'max_value',
        'min_length',
        'max_length',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function options()
    {
        return $this->hasMany(CategoryFieldOption::class);
    }
}
