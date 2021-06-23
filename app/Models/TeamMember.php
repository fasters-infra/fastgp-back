<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'member_id',
        'team_id',
        'is_team_leader'
    ];

    protected $hidden = [
        'pivot',
    ];

    public function member(){
        return $this->belongsTo(User::class, 'member_id');
    }

    public function team(){
        return $this->belongsTo(Team::class, 'team_id');
    }
}
