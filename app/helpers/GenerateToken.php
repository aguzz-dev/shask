<?php

namespace App\Helpers;

class GenerateToken
{
    public static function auth() {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $token = '';
        for ($i = 0; $i < 250; $i++) {
            $token .= $chars[rand(0, 61)];
        }
        return $token;
    }
}
