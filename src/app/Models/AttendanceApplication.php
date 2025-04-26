<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceApplication extends Model
{
    use HasFactory;
    protected $fillable = [
        'clock_in_change',
        'clock_out_change',
        'remark_change',
        'attendance_total',
    ];
    public function attendances(){
        return $this->belongsTo(Attendance::class);
    }
}
