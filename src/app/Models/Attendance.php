<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;
    protected $fillable = [
        'clock_in',
        'clock_out',
        'remark',
        'attendance_total',
    ];
    public function users(){
        return $this->belongsTo(User::class);
    }
}
