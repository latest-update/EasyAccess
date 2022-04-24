<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Custom\ShortResponse;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class RoleController extends Controller
{

    public function roles (): JsonResponse
    {
        return ShortResponse::json(Role::all());
    }

    public function usersByRole (Role $role): JsonResponse
    {
        return ShortResponse::json($role->users);
    }

    public function create (Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'permission' => 'required|string|max:255'
        ]);
        return ShortResponse::json(Role::create($data), 201);
    }

    public function update (Request $request, Role $role): JsonResponse
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'permission' => 'nullable|string|max:255'
        ]);

        $role->update($data);
        return ShortResponse::json($role);

    }

    public function delete (Role $role) : JsonResponse
    {
        if( App::environment('production') )
            return ShortResponse::errorMessage('Can\'t delete in production');

        $role->delete();
        return ShortResponse::json(['message' => 'Role was deleted']);
    }
}
