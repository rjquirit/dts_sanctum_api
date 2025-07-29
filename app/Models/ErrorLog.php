<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ErrorLog extends Model
{
    protected $casts = [
        'additional_data' => 'array',
        'created_at' => 'datetime',
    ];

    protected $fillable = [
        'message',
        'stack_trace',
        'url',
        'user_agent',
        'method',
        'status_code',
        'type',
        'user_id',
        'environment',
        'additional_data',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeBackend($query)
    {
        return $query->where('type', 'backend');
    }

    public function scopeFrontend($query)
    {
        return $query->where('type', 'frontend');
    }

    public function scopeCritical($query)
    {
        return $query->where('status_code', '>=', 500);
    }

    public static function getErrorStats()
    {
        return self::select([
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN type = "frontend" THEN 1 ELSE 0 END) as frontend'),
                DB::raw('SUM(CASE WHEN type = "backend" THEN 1 ELSE 0 END) as backend'),
                DB::raw('SUM(CASE WHEN status_code >= 500 THEN 1 ELSE 0 END) as critical')
            ])
            ->groupBy('date')
            ->orderBy('date', 'DESC')
            ->get();
    }
}
