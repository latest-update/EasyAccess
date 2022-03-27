<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Custom\Login;
use App\Http\Controllers\Custom\ShortResponse;
use App\Models\Card;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function users (Request $request): JsonResponse
    {
        if ( $request->user()->tokenCan('role-admin') )
            return ShortResponse::json(true, 'All users retrieved...', User::all());

        return ShortResponse::json(true, 'User information retrieved',  $request->user());
    }

    public function getSelf (Request $request): JsonResponse
    {
        return ShortResponse::json(true, 'Information about user has received', $request->user());
    }

    public function userById (Request $request, User $userid): JsonResponse
    {
        return ShortResponse::json(true, 'User by id retrieved', $userid);
    }

    public function register (Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|min:2|max:50',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', Password::min(8)],
            'born' => 'required|date|date_format:Y-m-d',
            'job_entry' => 'required|date|date_format:Y-m-d',
            'image' => 'nullable|string'
        ]);
        $data['password'] = bcrypt($data['password']);

        $card = Card::create([
            'serial' => mt_rand(220000105, 220100999),
            'token' => Str::random(32)
        ]);

        $data['card_id'] = $card->id;

        $response['user_data'] = User::create($data);
        $response['card_data'] = $card;

        return ShortResponse::json(true, 'User register successfully!', $response, 201);
    }

    public function login (Request $request): JsonResponse
    {
        if( Auth::attempt(['email' => $request->email, 'password' => $request->password ]) ){
            return Login::in(Auth::user());
        }
        return ShortResponse::json(false, 'Not correct email / password', [], 201);
    }

    public function editRole (Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'role_id' => 'required|integer'
        ]);

        $user->update($data);
        return ShortResponse::json(true, 'User role updated', $user);
    }

    public function update (Request $request, User $user): JsonResponse
    {
        if( $request->user()->id != $user->id and !$request->user()->tokenCan('role-admin') )
            return ShortResponse::json(false, 'Not found', [], 403);

        $data = $request->validate([
            'name' => 'nullable|string|min:2|max:50',
            'email' => 'nullable|email|unique:users,email',
            'image' => 'nullable|string'
        ]);

        $user->update($data);
        return ShortResponse::json(true, 'User updated', $user);

    }

    public function changePassword (Request $request, User $user): JsonResponse
    {
        if( $request->user()->id != $user->id )
            return ShortResponse::json(false, 'Not found', [], 403);

        $data = $request->validate([
            'old_password' => ['required', Password::min(8)],
            'password' => ['required', Password::min(8)]
        ]);

        if($user->password != $data['old_password'])
            return ShortResponse::errorMessage('Old password does not match');


        unset($data['old_password']);
        $user->update($data);
        return ShortResponse::json(true, 'User password updated', $user);
    }

    public function delete (Request $request, int $id): JsonResponse
    {
        if( $request->user()->id != $id and !$request->user()->tokenCan('ability:role-admin') )
            return ShortResponse::json(false, 'Not found', [], 403);

        return ShortResponse::delete(new User(), $id);
    }

}
