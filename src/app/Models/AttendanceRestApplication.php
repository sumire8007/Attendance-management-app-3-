<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRestApplication extends Model
{
    use HasFactory;
    protected $fillable = [
        'attendance_application_id',
        'rest_application_id',
        'approval_at',
    ];
    public function attendanceApplications()
    {
        return $this->belongsTo(AttendanceApplication::class);
    }
    public function restApplications()
    {
        return $this->belongsTo(RestApplication::class);
    }

}
