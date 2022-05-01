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
            return ShortResponse::json(User::all());

        return ShortResponse::json($request->user());
    }

    public function getSelf (Request $request): JsonResponse
    {
        return ShortResponse::json($request->user());
    }

    public function userById (Request $request, User $userid): JsonResponse
    {
        return ShortResponse::json($userid);
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

        return ShortResponse::json($response, 201);
    }

    public function login (Request $request): JsonResponse
    {
        if( Auth::attempt(['email' => $request->email, 'password' => $request->password ]) )
            return Login::in( Auth::user() );

        return ShortResponse::json(['message' => 'Invalid login or password'], 401);
    }

    public function editRole (Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'role_id' => 'required|integer'
        ]);

        $user->update($data);
        return ShortResponse::json($user);
    }

    public function update (Request $request, User $user): JsonResponse
    {
        if( $request->user()->id != $user->id and !$request->user()->tokenCan('role-admin') )
            return ShortResponse::json([], 403);

        $data = $request->validate([
            'name' => 'nullable|string|min:2|max:50',
            'email' => 'nullable|email|unique:users,email',
            'image' => 'nullable|string'
        ]);

        $user->update($data);
        return ShortResponse::json($user);

    }

    public function changePassword (Request $request, User $user): JsonResponse
    {
        if( $request->user()->id != $user->id )
            return ShortResponse::json(null, 403);

        $data = $request->validate([
            'old_password' => ['required', Password::min(8)],
            'password' => ['required', Password::min(8)]
        ]);

        if($user->password != $data['old_password'])
            return ShortResponse::errorMessage('Old password does not match');


        unset($data['old_password']);
        $user->update($data);
        return ShortResponse::json($user);
    }

    public function delete (Request $request, User $user): JsonResponse
    {
        if( $request->user()->id != $user->id and !$request->user()->tokenCan('ability:role-admin') )
            return ShortResponse::json(403);

        $user->delete();
        return ShortResponse::json(['message' => 'User was deleted']);
    }

}
