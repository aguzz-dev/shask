<?php

namespace App\Helpers;

class JsonResponse
{
    public static function send($status, $message, $status_code = 200, $data = null)
    {
        header('Content-Type: application/json');
        http_response_code($status_code);
        if(is_null($data)){
            echo json_encode([
                'status' => $status ? 'success' : 'error',
                'message' => $message
            ]);
        }else{
            echo json_encode([
                'status' => $status ? 'success' : 'error',
                'message' => $message,
                'data' => $data
            ]);
        }
        exit;
    }

    public static function exception($e)
    {
        header('Content-Type: application/json');
        http_response_code($e->getCode());
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
        exit;
    }
}
