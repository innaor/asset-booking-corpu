<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Asset;
use App\Models\User;

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

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}