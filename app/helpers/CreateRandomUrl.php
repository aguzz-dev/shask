<?php

namespace App\Helpers;

class CreateRandomUrl
{
    public static function publishPost() {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $url = '';
        for ($i = 0; $i < 4; $i++) {
            $url .= $chars[rand(0, 61)];
        }
        return $url;
    }
}