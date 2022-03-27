<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Custom\ShortResponse;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{

    public function roles (): JsonResponse
    {
        return ShortResponse::json(true, 'Roles retrieved...', Role::all());
    }

    public function usersByRole (Role $role): JsonResponse
    {
        return ShortResponse::json(true, 'Users by roles retrieved...', $role->users);
    }

    public function create (Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'permission' => 'required|string|max:255'
        ]);
        return ShortResponse::json(true, 'Role created!', Role::create($data), 201);
    }

    public function update (Request $request, Role $role): JsonResponse
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'permission' => 'nullable|string|max:255'
        ]);

        $role->update($data);
        return ShortResponse::json(true, 'Role updated', $role);

    }

    public function delete (int $id) : JsonResponse
    {
        return ShortResponse::delete(new Role(), $id);
    }

}
