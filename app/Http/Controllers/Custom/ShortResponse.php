<?php


namespace App\Http\Controllers\Custom;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use ReflectionClass;

class ShortResponse
{
    public static function json(bool $status, string $message, $data, int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    public static function update(Model $model, int $id, $data): JsonResponse
    {
        $row = $model::find($id);
        $modelName = AssumeClass::getClassName($model);
        if ($row)
        {
            $row->update($data);
            return self::json(true, $modelName . ' changed', $row);
        }
        else
        {
            return self::json(false, $modelName . ' not found', null, 404);
        }
    }

    public static function delete(Model $model, int $id) : JsonResponse
    {
        $row = $model::find($id);
        $modelName = AssumeClass::getClassName($model);
        if ($row)
        {
            $row->delete($id);
            return self::json(true, $modelName . ' was deleted', null);
        }
        else
        {
            return self::json(false, $modelName . ' not found', null, 404);
        }
    }

    public static function errorMessage (string $error) : JsonResponse
    {
        return response()->json([
            'status' => false,
            'errors' => $error
        ], 404);
    }

}
