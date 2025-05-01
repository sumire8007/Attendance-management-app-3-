<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class BreakTime extends Model
{
    use HasFactory;
    protected $fillable = [
        'break_in_at',
        'break_out_at',
        'break_total',
    ];
    public function users(){
        return $this->belongsTo(User::class);
    }
}
