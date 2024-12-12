<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function successResponse($data, $message = "Success", $status = 200)
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    public function errorResponse($message = "Error", $status = 400, $errors = null)
    {

        if ($errors) {
            $response['errors'] = $errors;
        }
        return response()->json([
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}
