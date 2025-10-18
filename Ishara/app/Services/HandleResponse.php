<?php
namespace App\Services;

use Illuminate\Http\JsonResponse;

class HandleResponse
{

    public static function success($message, $data = null, $status = 200, $headers = [], $options = 0): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $status, $headers, $options);
    }

    public static function fail($message, $errors = [], $status = 422, $headers = [], $options = 0): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $status, $headers, $options);
    }
}