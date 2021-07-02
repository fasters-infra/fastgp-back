<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'street',
        'number',
        'complement',
        'neighborhood',
        'zipcode',
        'city_id'
    ];

    public function city() {
        return $this->belongsTo(City::class);
    }
}
