<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EletronicPointMarking extends Model
{
    use HasFactory;

    protected $fillable = [
        'justification',
        'need_justification',
        'user_id',
        'justified_by'
    ];
}
