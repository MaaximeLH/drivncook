<?php

namespace App\Controllers\Admin;

use App\Entity\Admin;
use App\Entity\Customer;
use App\Entity\Orders;
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

            $usersMinCommandFilter = (isset($params['users_min_commands'])) ? trim(intval($params['users_min_commands'])) : 0;
            $customersMinCommandFilter = (isset($params['customers_min_commands'])) ? trim(intval($params['customers_min_commands'])) : 0;

            $em = Entity::getEntityManager();
            $usersRepository = $em->getRepository(Users::class);
            $ordersRepository = $em->getRepository(Orders::class);
            $customersRepository = $em->getRepository(Customer::class);

            $emails = [];

            if($usersFilter) {
                foreach ($usersRepository->findAll() as $user) {
                    $total = count($ordersRepository->findByUser($user));
                    if($total > $usersMinCommandFilter) {
                        $emails[] = $user->getEmail();
                    }
                }
            }

            if($customersFilter) {
                foreach ($customersRepository->findAll() as $customer) {
                    $total = count($ordersRepository->findByCustomer($customer));
                    if($total > $customersMinCommandFilter) {
                        $emails[] = $customer->getEmail();
                    }
                }
            }

            $emails = implode(', ', $emails);

            //TODO: Envoie de l'email avec le contenu etc...
            return View::render('Admin/newletters', ['admin' => $this->admin, 'page' => 'newletters', 'success' => true, 'params' => $params]);

        }

        return View::render('Admin/newletters', ['admin' => $this->admin, 'page' => 'newletters']);
    }
}