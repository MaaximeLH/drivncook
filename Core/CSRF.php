<?php

namespace Core;

class CSRF {

    public static function generate() {
        $token = md5(bin2hex(openssl_random_pseudo_bytes(6)));
        Session::set('csrf_token', $token);
        return $token;
    }

    public static function isValid($token = false) {
        $token = (!$token) ? Session::get('csrf_token') : $token;

        if($token == false) {
            return false;
        }

        if($token === Session::get('csrf_token')) {
            return true;
        }

        return false;
    }

    public static function validate($throw = true) {
        if(!self::isValid()) {
            if($throw) {
                throw new \Exception("Le token CSRF est invalide.");
            }

            return false;
        }

        return true;
    }

    public static function fields() {
        return '<input type="hidden" name="csrf_token" value="'. Session::get('csrf_token') .'">';
    }
}