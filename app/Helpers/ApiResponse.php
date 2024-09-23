<?php
namespace App\Helpers;


class ApiResponse {

    static function sendResponse($code = 200, $message = null, $data = null) {
        $response = [
            'code' => $code,
            'message' => $message,
            'data' => $data
        ];

        return response()->json($response, $code);
    }
}