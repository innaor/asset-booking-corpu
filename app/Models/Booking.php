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
        'status',
        // Check-in
        'checkin_condition',
        'checkin_note',
        'checkin_photo',
        'checkin_at',
        'checkin_by',
        // Check-out
        'checkout_condition',
        'checkout_note',
        'checkout_photo',
        'checkout_at',
        'checkout_by',
    ];

    protected $casts = [
        'checkin_at'  => 'datetime',
        'checkout_at' => 'datetime',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function checkinBy()
    {
        return $this->belongsTo(User::class, 'checkin_by');
    }

    public function checkoutBy()
    {
        return $this->belongsTo(User::class, 'checkout_by');
    }
}