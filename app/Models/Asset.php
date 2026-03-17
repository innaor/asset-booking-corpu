<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'subcategory_id',
        'status'
    ];

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }
}