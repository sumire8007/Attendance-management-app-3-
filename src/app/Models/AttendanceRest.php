<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRest extends Model
{
    use HasFactory;
    protected $fillable = [
        'attendance_id',
        'rest_id',
    ];
    public function attendances()
    {
        return $this->belongsTo(Attendance::class);
    }
    public function rest()
    {
        return $this->belongsTo(Rest::class);
    }

}
