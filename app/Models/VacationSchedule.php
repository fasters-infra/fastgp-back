<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VacationSchedule extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'initial_date',
        'end_date',
        'user_id',
        'approver_id',
        'state'
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approver(){
        return $this->belongsTo(User::class, 'approver_id');
    }
}
