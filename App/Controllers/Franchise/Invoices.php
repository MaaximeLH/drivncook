<?php

namespace App\Controllers\Franchise;

use App\Entity\Invoice;
use App\Entity\InvoiceLine;
use App\Entity\Users;
use Core\Controller;
use Core\CSRF;
use Core\Entity;
use Core\Request;
use Core\Session;
use Core\Stripe;
use Core\View;

/**
 * class Home, cette classe est utilisée pour la page d'accueil et le login / logout
 * @package App\Controllers\Franchise
 */
class Invoices extends Controller
{
    protected function logged()
    {
        return Session::get('user_id');
    }

    public function receivedAction() {
        $userId = $this->logged();
        if(!$userId) {
            return $this->redirectTo('/panel/login');
        }

        $em = Entity::getEntityManager();
        $user = $em->find(Users::class, $userId);

        $invoicesRepository = $em->getRepository(Invoice::class);
        $invoices = $invoicesRepository->findByUser($user);

        return View::render('Franchise/invoicesReceived', ['user' => $user, 'page' => 'invoices_received', 'invoices' => $invoices]);
    }

    public function seeAction() {
        $userId = $this->logged();
        if(!$userId) {
            return $this->redirectTo('/panel/login');
        }

        $em = Entity::getEntityManager();
        $user = $em->find(Users::class, $userId);

        $invoicesRepository = $em->getRepository(Invoice::class);
        $invoice = $invoicesRepository->find($this->getRouteParameter('id'));
        if(is_null($invoice) || is_null($invoice->getUser()) || $invoice->getUser()->getId() != $user->getId()) {
            return $this->redirectTo('/panel');
        }

        $invoiceLineEntity = $em->getRepository(InvoiceLine::class);
        $lines = $invoiceLineEntity->findByInvoice($invoice);

        return View::render('Franchise/invoice', ['page' => 'invoices', 'user' => $user, 'invoice' => $invoice, 'lines' => $lines]);
    }

    public function payInvoiceAction() {
        $userId = $this->logged();
        if(!$userId) {
            return $this->redirectTo('/panel/login');
        }

        $em = Entity::getEntityManager();
        $user = $em->find(Users::class, $userId);

        $invoiceRepository = $em->getRepository(Invoice::class);
        $invoice = $invoiceRepository->find($this->getRouteParameter('id'));

        $total = $this->countTotalInvoice($invoice);

        if(is_null($invoice) || is_null($invoice->getUser()) || $invoice->getUser()->getId() != $user->getId() || $invoice->getOwner() != "drivncook" || $invoice->getStatus()) {
            return $this->redirectTo('/panel');
        }

        CSRF::generate();
        if(Request::isPost()) {
            CSRF::validate();
            $params = Request::getAllParams();

            if(empty($params['stripeToken'])) {
                return $this->redirectTo('/panel/invoices/' . $invoice->getId());
            }

            $stripe = new Stripe();
            $stripe->setSource($params['stripeToken']);

            $stripe
                ->setCustomerName($user->getFirstname() . ' ' . $user->getLastname())
                ->setCustomerEmail($user->getEmail())->createCustomer();

            $stripe->setChargeAmount($total);

            if(!is_null($invoice->getWarehouse())) {
                $stripe->setChargeDescription("Approvisionnement d'un franchisé - Entrepôt de " . $invoice->getWarehouse()->getName());
            }

            $charge = $stripe->createCharge();

            if($charge['status'] == "succeeded") {
                $invoice->setStatus(true);
                $em->flush();
                return $this->redirectTo('/panel/invoices/' . $invoice->getId());
            } else {
                return View::render('Franchise/payInvoice', ['user' => $user, 'page' => 'invoices_received', 'invoice' => $invoice, 'total' => $total, 'payment' => false]);
            }
        }

        return View::render('Franchise/payInvoice', ['user' => $user, 'page' => 'invoices_received', 'invoice' => $invoice, 'total' => $total]);
    }

    private function countTotalInvoice($invoice) {
        $em = Entity::getEntityManager();
        $invoiceLineRepository = $em->getRepository(InvoiceLine::class);

        $lines = $invoiceLineRepository->findByInvoice($invoice);
        if(is_null($lines)) return 0;

        $total = 0;
        foreach ($lines as $line) {
            $subtotal = ($line->getPrice() * $line->getQuantity());
            $subtotal += $subtotal * ($line->getTva() / 100);
            $total += $subtotal;
        }

        return $total;
    }
}