<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'attendance_date',
        'clock_in_at',
        'clock_out_at',
        'remark',
        'attendance_total',
    ];
    public function user(){
        return $this->belongsTo(User::class);
    }
}
