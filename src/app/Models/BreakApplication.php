<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakApplication extends Model
{
    use HasFactory;
    protected $fillable = [
        'break_in_change',
        'break_out_change',
        'break_total',
    ];
    public function breaks(){
        return $this->belongsTo(BreakTime::class);
    }
}
