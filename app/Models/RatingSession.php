<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RatingSession extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'rating_id',
        'session_id',
    ];

    public function rating(){
        return $this->belongsTo(Rating::class);
    }

    public function session(){
        return $this->belongsTo(Session::class)->with('sub_sessions');
    }
}
