<?php

namespace App\Http\Traits;

trait ApiResponse
{
    public function successResponse($httpStatusCode, $message, $data = null)
    {
        return response()->json([
            "success" => true,
            "message" => $message,
            "data" => $data
        ], $httpStatusCode);
    }

    public function failedResponse($httpStatusCode, $message)
    {
        return response()->json([
            "success" => false,
            "message" => $message
        ], $httpStatusCode);
    }
}
