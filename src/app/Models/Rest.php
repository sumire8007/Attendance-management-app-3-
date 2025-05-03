<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rest extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'rest_date',
        'rest_in_at',
        'rest_out_at',
        'rest_total',
    ];
    public function users()
    {
        return $this->belongsTo(User::class);
    }

}
