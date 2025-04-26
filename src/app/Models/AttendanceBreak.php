<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceBreak extends Model
{
    use HasFactory;
    public function attendances(){
        return $this->belongsToMany(Attendance::class);
    }
    public function breaks(){
        return $this->belongsToMany(BreakTime::class);
    }
}
