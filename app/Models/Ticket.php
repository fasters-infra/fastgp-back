<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\HasRoles;

class Ticket extends Model
{
    use HasFactory, HasRoles, HasPermissions;

    protected $table = "ticket";

    protected $fillable = ["user_id", "name", "topic", "departament", "message", "archive"];
}
