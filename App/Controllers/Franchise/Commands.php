<?php

namespace App\Controllers\Franchise;

use App\Entity\OrderLine;
use App\Entity\Orders;
use App\Entity\Users;
use Core\Controller;
use Core\Entity;
use Core\Session;
use Core\View;

class Commands extends Controller {

    private $user;

    public function __construct($routes)
    {
        parent::__construct($routes);

        $userId = Session::get('user_id');
        $em = Entity::getEntityManager();
        if(is_null($user = $em->find(Users::class, $userId))) {
            return $this->redirectTo('/panel/login');
        }

        $this->user = $user;
    }

    public function indexAction() {
        $em = Entity::getEntityManager();
        $ordersRepository = $em->getRepository(Orders::class);
        $orderLineRepository = $em->getRepository(OrderLine::class);

        $orders = [];

        $orders['status_1'] = $ordersRepository->findBy(['user' => $this->user, 'status' => 1]); // Non traîtée
        $orders['status_2'] = $ordersRepository->findBy(['user' => $this->user, 'status' => 2]); // En traitement
        $orders['status_3'] = $ordersRepository->findBy(['user' => $this->user, 'status' => 3]); // Terminée

        foreach ($orders as $key => $_orders) {
            foreach ($_orders as $_key => $value) {
                $orders[$key][$_key]->lines = $orderLineRepository->findByOrder($value);
            }
        }

        return View::render('Franchise/commands', ['user' => $this->user, 'page' => 'commands', 'orders' => $orders]);

    }

}