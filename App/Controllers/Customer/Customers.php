<?php

namespace App\Controllers\Customer;

use App\Entity\Customer;
use Core\Controller;
use Core\Entity;
use Core\Session;
use Core\View;

class Customers extends Controller {

    private $user;

    public function __construct($routes)
    {
        parent::__construct($routes);

        $userId = Session::get('customer_id');
        $em = Entity::getEntityManager();
        if (is_null($user = $em->find(Customer::class, $userId))) {
            return $this->redirectTo('/customers/login');
        }

        $this->user = $user;
    }

    public function indexAction() {
        $em = Entity::getEntityManager();
        $repository = $em->getRepository(Customer::class);

        $customer = $repository->find(Session::get('customer_id'));

        return View::render('Customers/index', ['customer' => $customer, 'page' => 'index']);
    }

}