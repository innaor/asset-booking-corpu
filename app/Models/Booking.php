<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'user_id',
        'guest_name',
        'guest_phone',
        'guest_document',
        'date',
        'start_time',
        'end_time',
        'status'
    ];
}