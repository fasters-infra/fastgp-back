<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequiredFields extends Model
{
    use HasFactory;

    protected $table = 'required_fields';

    protected $fillable = [

        'company_id',
        'type',
        'name',
        'visible',
        'required',

    ];
}
