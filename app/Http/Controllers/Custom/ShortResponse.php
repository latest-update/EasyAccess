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

    public static function delete(Model $model, int $id) : JsonResponse
    {
        $row = $model::find($id);
        $modelName = AssumeClass::getClassName($model);
        if ($row)
        {
            $row->delete($id);
            return self::json(null);
        }
        else
        {
            return self::json(null, 404);
        }
    }

    public static function errorMessage (string $error) : JsonResponse
    {
        return response()->json([
            'errors' => $error
        ], 404);
    }

}
