<?php


namespace App\Http\Controllers\Custom;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Laravel\Sanctum\PersonalAccessToken as UserToken;

class Login
{
    public static function in(User $user): JsonResponse
    {
        UserToken::where('tokenable_id', $user->id)->delete();
        $response['token'] = $user->createToken('old', [$user->role->permission])->plainTextToken;
        $response['info'] = $user;

        $user->remember_token = $response['token'];
        $user->save();

        return ShortResponse::json($response, 200);
    }
}
