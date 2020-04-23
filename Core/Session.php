<?php

namespace Core;

class Session
{
    public static function has($key) {
        return isset($_SESSION[$key]) ? true : false;
    }

    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public static function get($key) {
        return (isset($_SESSION[$key])) ? $_SESSION[$key] : false;
    }

    public static function destroy()
    {
        $_SESSION = [];
        unset($_SESSION);
    }
}
