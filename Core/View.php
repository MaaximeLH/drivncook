<?php

namespace Core;

class View
{
    public static function render($view, $args = [], $extension = 'phtml') {
        extract($args, EXTR_SKIP);

        $file = dirname(__DIR__) . "/App/Views/" . $view . '.' .$extension;

        if (is_readable($file)) {
            require $file;
        } else {
            throw new \Exception("$file non trouvé.");
        }

        return true;
    }
}
