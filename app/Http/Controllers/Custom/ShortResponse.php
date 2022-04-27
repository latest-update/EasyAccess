<?php


namespace App\Http\Controllers\Custom;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use ReflectionClass;

class ShortResponse
{
    public static function json($data, int $statusCode = 200): JsonResponse
    {
        return response()->json($data, $statusCode);
    }

    public static function errorMessage (string $error) : JsonResponse
    {
        return response()->json([
            'errors' => $error
        ], 404);
    }

}
