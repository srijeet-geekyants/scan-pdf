<?php

namespace App\Helpers;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;

class ResponseHelper
{
    public static function success($data = null, $statusCode = Response::HTTP_OK, $statusMessage = 'OK')
    {
        $data = $data instanceof ResourceCollection ? $data->response()->getData() : $data;

        $payload = [
            'payload' => $data,
            'success' => true,
            'statusMessage' => $statusMessage,
        ];

        return response($payload, $statusCode);
    }

    public static function successWithMessageAndStatus($statusMessage, $statusCode)
    {
        return self::success(null, $statusCode, $statusMessage);
    }

    public static function successWithStatus($statusCode)
    {
        return self::success(null, $statusCode);
    }

    public static function successWithMessage($statusMessage)
    {
        return self::success(null, Response::HTTP_OK, $statusMessage);
    }

    public static function successWithData($message = 'OK', $data = null) {
        return self::success($data, Response::HTTP_OK, $message);
    }

    public static function error($data = null, $statusCode = Response::HTTP_OK, $statusMessage = 'Error')
    {
        $data = $data instanceof ResourceCollection ? $data->response()->getData() : $data;

        $payload = [
            'payload' => $data,
            'statusCode' => $statusCode,
            'success' => false,
            'statusMessage' => $statusMessage,
        ];

        return response($payload, $statusCode);
    }

    public static function errorWithMessageAndStatus($statusMessage, $statusCode)
    {
        return self::error(null, $statusCode, $statusMessage);
    }

    public static function errorWithStatus($statusCode)
    {
        return self::error(null, $statusCode);
    }

    public static function errorWithMessage($statusMessage)
    {
        return self::error(null, Response::HTTP_OK, $statusMessage);
    }
}
