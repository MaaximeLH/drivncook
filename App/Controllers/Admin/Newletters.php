<?php

namespace App\Controllers\Admin;

use App\Entity\Admin;
use App\Entity\Customer;
use App\Entity\Users;
use Core\Controller;
use Core\CSRF;
use Core\Entity;
use Core\Request;
use Core\Session;
use Core\View;

class Newletters extends Controller {

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

    public function indexAction() {
        CSRF::generate();

        if(Request::isPost()) {
            CSRF::validate();
            $params = Request::getAllParams();

            $usersFilter = (isset($params['send_all_users']));
            $customersFilter = (isset($params['send_all_customers']));

            if($usersFilter == false && $customersFilter == false) {
                return View::render('Admin/newletters', ['admin' => $this->admin, 'page' => 'newletters', 'error' => true, 'params' => $params]);
            }

            $em = Entity::getEntityManager();
            $usersRepository = $em->getRepository(Users::class);
            $customersRepository = $em->getRepository(Customer::class);

            //TODO: SI $usersFilter ; foreach des users ; récupération des emails si les filtres sont OK
            // TODO: SI $customersFilter ; foreach des customers ; récupération des emails si les filtres sont OK
        }

        return View::render('Admin/newletters', ['admin' => $this->admin, 'page' => 'newletters']);
    }
}