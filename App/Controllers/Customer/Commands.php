<?php

namespace App\Controllers\Customer;

use App\Entity\Customer;
use App\Entity\OrderLine;
use App\Entity\Orders;
use Core\Entity;
use Core\Session;
use Core\View;

class Commands extends \Core\Controller {

    private $em;
    private $customer;

    public function __construct($routes)
    {
        parent::__construct($routes);
        $customerId = Session::get('customer_id');
        $em = Entity::getEntityManager();
        $this->em = $em;

        $customerRepository = $em->getRepository(Customer::class);
        $customer = $customerRepository->find($customerId);

        if($customer === false) {
            return $this->redirectTo('/customers/login');
        }

        $this->customer = $customer;
    }

    public function indexAction() {
        $ordersRepository = $this->em->getRepository(Orders::class);
        $orderLineRepository = $this->em->getRepository(OrderLine::class);
        $orders = $ordersRepository->findByCustomer($this->customer);

        foreach ($orders as $key => $order) {
            $lines = $orderLineRepository->findByOrder($order);
            $total = 0;
            foreach ($lines as $line) {
                $total += $line->getPrice();
            }

            $orders[$key]->priceTotal = $total;
        }

        return View::render('Customers/commands', ['customer' => $this->customer, 'orders' => $orders, 'page' => 'commands']);
    }

}