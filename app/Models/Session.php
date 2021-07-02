<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'order',
        'color'
    ];

    protected $hidden = [
        'pivot',
    ];


    public function sub_sessions(){
        return $this->hasMany(SubSession::class)->orderBy('order');
    }
}
