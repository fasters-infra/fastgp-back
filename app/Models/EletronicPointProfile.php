<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EletronicPointProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'entry_time',
        'break_time',
        'interval_return_time',
        'departure_time',
        'tolerance'
    ];

    public function designs(){
        $this->hasMany(User::class);
    }
}
