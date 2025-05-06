<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestApplication extends Model
{
    use HasFactory;
    protected $fillable = [
        'rest_id',
        'rest_change_date',
        'rest_in_change_at',
        'rest_out_change_at',
        'rest_change_total',
    ];
    public function rests()
    {
        return $this->belongsTo(Rest::class);
    }

}
