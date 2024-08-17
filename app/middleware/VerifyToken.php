<?php

namespace App\Middleware;

use App\Helpers\getHeader;
use App\Models\PersonalAccessToken;
use App\Http\Controllers\Controller;

class VerifyToken
{
    public static function jwt()
    {
        (new PersonalAccessToken)->validateToken(str_replace('Bearer ', '', (string)$_SERVER['HTTP_AUTHORIZATION']));
    }
}
