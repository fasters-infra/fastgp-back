<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsController extends Controller
{

    public function index()
    {
        $permissions = Permission::get();
        return $permissions;
    }

    public function show()
    {
        return auth()->user()->permissions;
    }

    public function create(Request $request)
    {

        $permission = Permission::findById($request->input('permission_id'));
        $role = Role::findById($request->input('role_id'));
        $role->givePermissionTo($permission);
        $permission->assignRole($role);

        return compact('role');
    }

    public function store(Request $request)
    {

        $permission = Permission::create(['name' => $request->input('permission')]);
        return compact('permission');
    }
}
