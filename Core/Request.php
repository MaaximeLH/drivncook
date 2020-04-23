<?php

namespace Core;

class Request {

    public static function isPost() {
        return ($_SERVER['REQUEST_METHOD'] == 'POST' ? true : false);
    }

    protected static function isGet() {
        return ($_SERVER['REQUEST_METHOD'] == 'GET' ? true : false);
    }

    public static function isAjax() {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    }

    public static function assertPostOnly(){
        if(!static::isPost()){
            throw new \Exception("Cette action n'est disponible qu'en POST, pressez le bouton arrière et recommencez.");
        }
    }

    public static function getParam($key, $default = null) {
        if (static::isPost()) {
            if(isset($_POST[$key])) {
                return $_POST[$key];
            }
        }
        else if (static::isGet()) {
            if(isset($_GET[$key])) {
                return $_GET[$key];
            }
        }

        return $default;
    }

    public static function getAllParams() {
        if (static::isPost()) {
            return $_POST;
        }
        else if (static::isGet()) {
            return $_GET;
        }
    }

    public static function getFiles() {
        if (static::isPost()) {
            return $_FILES;
        }
    }

    public static function getFile($key) {
        if(static::isPost()) {
            return $_FILES[$key] ?? null;
        }
    }
}