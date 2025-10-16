<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController {
    use AuthorizesRequests, ValidatesRequests;

    public function validationMessage($errors) {

        return response()->json([
            'status'  => false,
            'message' => $errors,
        ]);
    }

    public function successMessage($message = null, $data = null) {
        return response()->json([
            'status'  => true,
            'message' => $message ?? 'Data fetched successfully',
            'data'    => $data,
        ]);
    }

    public function errorMessage($message = null, $data = null) {
        return response()->json([
            'status'  => false,
            'message' => $message ?? 'Data fetched successfully',
            'data'    => $data,
        ]);
    }
}
