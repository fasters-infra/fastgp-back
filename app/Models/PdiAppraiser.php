<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PdiAppraiser extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'appraiser_id',
        'rating_id',
    ];

    protected $hidden = [
        'id',
        'rating_id',
        'created_at',
        'updated_at',
        'pivot'
    ];

    public function appraiser(){
        return $this->belongsTo(User::class, 'appraiser_id');
    }

}
