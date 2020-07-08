<?php

namespace App\Controllers\Customer;

use App\Entity\Customer;
use App\Entity\EventCustomer;
use Core\Controller;
use Core\Entity;
use Core\Session;
use Core\View;

class Events extends Controller {

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

        if(is_null($customer)) {
            $this->redirectTo('/customers/login');
        }

        $this->customer = $customer;
    }

    public function indexAction() {
        $eventCustomerRepository = $this->em->getRepository(EventCustomer::class);
        $events = $eventCustomerRepository->findByIdCustomer($this->customer);

        return View::render('Customers/events', ['page' => 'events', 'customer' => $this->customer, 'events' => $events]);
    }

    public function unsubscribeAction() {
        $eventCustomerRepository = $this->em->getRepository(EventCustomer::class);
        $event = $eventCustomerRepository->findOneBy(['id' => $this->getRouteParameter('id'), 'idCustomer' => $this->customer]);

        if(is_null($event)) {
            $this->redirectTo('/customers/events');
        }

        $this->em->remove($event);
        $this->em->flush();

        $this->redirectTo('/customers/events');
    }
}