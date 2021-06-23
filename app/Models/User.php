<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;



class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles, HasPermissions;

    protected $table = "users";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $guard_name = 'api';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'pivot'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $with = [
        'permissions',
        'roles'
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Obtem a cidade do usuário.
     */
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Obtem o perfil dop usuario.
     */
    public function hasRoles()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Obtem as permissões do usuário.
     */
    public function hasPermissions()
    {
        return $this->belongsTo(Permission::class);
    }
    public function stacks()
    {
        return $this->belongsToMany(Stack::class, 'user_stacks');
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_members', 'member_id');
    }

    public function userOccupations()
    {
        return $this->hasMany(UserOccupation::class, 'user_id');
    }

    public function occupations()
    {
        return $this->belongsToMany(OccupationLevel::class, 'user_occupations', 'user_id', 'occupation_levels_id');
    }


    public function socialMedias()
    {
        return $this->hasMany(UserSocialMedia::class, 'user_id');
    }

    public function eletronicPointProfile()
    {
        return $this->belongsTo(EletronicPointProfile::class);
    }
}
