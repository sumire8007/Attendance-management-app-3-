<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceBreakApplication extends Model
{
    use HasFactory;
    public function attendanceApplications(){
        return $this->belongsToMany(AttendanceApplication::class);
    }
    public function breakApplications(){
        return $this->belongsToMany(BreakApplication::class);
    }
}
