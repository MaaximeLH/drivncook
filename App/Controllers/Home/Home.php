<?php

namespace App\Controllers\Home;

use Core\Controller;
use Core\View;

class Home extends Controller {

    public function indexAction() {
        return View::render('Home/index');
    }

}