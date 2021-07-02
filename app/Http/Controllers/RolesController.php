<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;

class RolesController extends Controller
{
    public function index()
    {
        $roles = Role::get();

        return $roles;
    }

    public function rolesAndPermissions()
    {
        $roles = Role::get();
        $permissions = Permission::get();
        $roles->givePermissionTo($permissions);

        return $roles;
    }

    public function show($id)
    {
        $role = Role::find($id);
        $permissions = Permission::join("role_has_permissions", "role_has_permissions.permission_id", "=", "permissions.id")
            ->where("role_has_permissions.role_id", '=', $id)
            ->get();

        return compact('role', 'permissions');
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'name' => 'required|min:1|max:255',

        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        }

        $role = Role::create([
            'name' => $request->input('name'),
        ]);

        return $role;
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'permission' => 'required',
        ]);

        $role = Role::find($request->id);
        $role->save();

        $role->syncPermissions($request->permission);

        return 'ok';
    }

    public function destroy(Role $role)
    {
        $role->delete();

        return response()->json(
            'Perfil removido com sucesso!',
            200
        );
    }
}
