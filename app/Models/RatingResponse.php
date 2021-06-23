<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RatingResponse extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'rating_id',
        'sub_session_id',
        'legend_id',
    ];

    public function rating(){
        return $this->belongsTo(Rating::class);
    }

    public function sub_session(){
        return $this->belongsTo(SubSession::class);
    }

    public function legend(){
        return $this->belongsTo(Legend::class);
    }
}
