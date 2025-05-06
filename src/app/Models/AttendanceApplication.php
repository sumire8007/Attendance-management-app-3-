<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceApplication extends Model
{
    use HasFactory;
    protected $fillable = [
        'attendance_id',
        'attendance_change_date',
        'clock_in_change_at',
        'clock_out_change_at',
        'remark_change',
        'attendance_change_total',
    ];
    public function attendances(){
        return $this->belongsTo(Attendance::class);
    }
}
