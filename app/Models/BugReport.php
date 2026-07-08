<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BugReport extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'category',
        'related_page',
        'description',
        'attachment_path',
        'status',
        'admin_note',
        'handled_by',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function handledBy()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
    
    public function bugReports()
    {
        return $this->hasMany(BugReport::class, 'user_id');
    }
}