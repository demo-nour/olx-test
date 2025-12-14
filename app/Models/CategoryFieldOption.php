<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoryFieldOption extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'category_field_id',
        'value',
        'label',
        'external_id'
    ];

    public function field()
    {
        return $this->belongsTo(CategoryField::class, 'category_field_id');
    }
}
