<?php

namespace App\Http\HttpResponses;

class HttpResponse
{
    public static function successResponse($data, $message = '')
    {
        return response()->json([
            'success'   => true,
            'code'      =>  200,     
            'data' => $data,
            'message' => $message
        ], 200);
    }

    public static function unauthorizedResponse($data, $message = '')
    {
        return response()->json([
            'success'   => false,
            'code'      =>  401,     
            'data' => $data,
            'message' => $message
        ], 401);
    }

    public static function errorResponse($data, $message = '')
    {
        return response()->json([
            'success'   => false,
            'code'      =>  400,     
            'data' => $data,
            'message' => $message
        ], 400);
    }
}