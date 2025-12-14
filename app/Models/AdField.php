<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdField extends Model
{
    use HasFactory;

    protected $fillable = [
        'ad_id',
        'category_field_id',
        'value',
    ];

    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }

    public function categoryField()
    {
        return $this->belongsTo(CategoryField::class);
    }
}
