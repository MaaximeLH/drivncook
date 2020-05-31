<?php

namespace App\Controllers\Admin;

use App\Entity\Admin;
use Core\Controller;
use Core\CSRF;
use Core\Entity;
use Core\Request;
use Core\Session;
use Core\Validator;
use Core\View;

class Events extends Controller
{

    private $admin;

    public function __construct($routes)
    {
        parent::__construct($routes);

        $userId = Session::get('admin_id');
        $em = Entity::getEntityManager();
        if (is_null($admin = $em->find(Admin::class, $userId))) {
            return $this->redirectTo('/administration/login');
        }

        $this->admin = $admin;
    }

    public function indexAction()
    {
    }

    public function addAction()
    {
    }
}
