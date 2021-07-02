<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'schedule',
        'title',
        'description'
    ];

    protected $hidden = [
        'pivot',
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function appraisers(){
        return $this->hasMany(PdiAppraiser::class)->with('appraiser');
    }

    public function legends(){
        return $this->belongsToMany(Legend::class,'rating_legends');
    }

    public function sessions(){
        return $this->belongsToMany(Session::class, 'rating_sessions')->orderBy('order')->with('sub_sessions');
    }
}
