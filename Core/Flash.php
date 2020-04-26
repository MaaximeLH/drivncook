<?php

namespace Core;

class Flash {

    public static function set($key, $message) {
        Session::set($key, ['message' => $message]);
    }

    public static function has($key) {
        return Session::has($key);
    }

    public static function get($key) {
        $flash = Session::get($key);
        Session::delete($key);
        return $flash;
    }
}