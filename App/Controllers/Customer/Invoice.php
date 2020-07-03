<?php

namespace App\Controllers\Customer;

use App\Entity\Customer;
use Core\Entity;
use Core\Session;
use Core\View;

class Invoice extends \Core\Controller {

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
        $invoicesRepository = $this->em->getRepository(\App\Entity\Invoice::class);
        $invoices = $invoicesRepository->findByCustomer($this->customer);

        return View::render('Customers/invoices', ['customer' => $this->customer, 'page' => 'invoice', 'invoices' => $invoices]);
    }

    public function detailsAction() {
        $invoicesRepository = $this->em->getRepository(\App\Entity\Invoice::class);
        $invoiceLineRepository = $this->em->getRepository(\App\Entity\InvoiceLine::class);
        $invoice = $invoicesRepository->findOneBy(['id' => $this->getRouteParameter('id'), 'customer' => $this->customer]);
        $lines   = $invoiceLineRepository->findBy(['invoice' => $invoice]);

        return View::render('Customers/invoicesDetails', ['customer' => $this->customer, 'page' => 'invoice', 'invoice' => $invoice, 'lines' => $lines]);
    }

}