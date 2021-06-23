<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RatingLegend extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'rating_id',
        'legend_id',
    ];

    public function rating(){
        return $this->belongsTo(Rating::class);
    }

    public function legend(){
        return $this->belongsTo(Legend::class);
    }

}
