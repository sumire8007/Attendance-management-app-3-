<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRest extends Model
{
    use HasFactory;
    public function attendances()
    {
        return $this->belongsToMany(Attendance::class);
    }
    public function rest()
    {
        return $this->belongsToMany(Rest::class);
    }

}
