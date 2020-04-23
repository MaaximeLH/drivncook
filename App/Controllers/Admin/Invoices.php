<?php

namespace App\Controllers\Admin;

use App\Entity\Admin;
use App\Entity\Invoice;
use App\Entity\InvoiceLine;
use Core\Controller;
use Core\CSRF;
use Core\Entity;
use Core\Request;
use Core\Validator;
use Core\View;

class Invoices extends Controller {

    private function getUser($id) {
        $em = Entity::getEntityManager();
        $repository = $em->getRepository(Admin::class);
        $user = $repository->find($id);
        return $user;
    }

    public function receivedAction() {
        $userId = $this->logged();
        if(!$userId) {
            return $this->redirectTo('/administration/login');
        }

        $user = $this->getUser($userId);

        $em = Entity::getEntityManager();
        $invoiceEntity = $em->getRepository(Invoice::class);
        $invoices      = $invoiceEntity->findByRecipient('drivncook');

        foreach ($invoices as $key => $invoice) {
            $file = $this->searchInvoiceFile($invoice->getId());

            if($file == false) {
                $invoices[$key]->externalInvoice = false;
            } else {
                $invoices[$key]->externalInvoice = true;
            }
        }

        return View::render('Admin/invoicesReveived', ['user' => $user, 'page' => 'invoices_received', 'invoices' => $invoices]);
    }

    public function issuedAction() {
        $userId = $this->logged();
        if(!$userId) {
            return $this->redirectTo('/administration/login');
        }

        $user = $this->getUser($userId);

        $em = Entity::getEntityManager();
        $invoiceEntity = $em->getRepository(Invoice::class);
        $invoices      = $invoiceEntity->findByOwner('drivncook');

        return View::render('Admin/invoicesIssued', ['user' => $user, 'page' => 'invoices_issued', 'invoices' => $invoices]);
    }

    public function seeAction() {
        $userId = $this->logged();
        if(!$userId) {
            return $this->redirectTo('/administration/login');
        }

        $user = $this->getUser($userId);

        CSRF::generate();
        $em = Entity::getEntityManager();
        $invoiceEntity = $em->getRepository(Invoice::class);
        $invoiceLineEntity = $em->getRepository(InvoiceLine::class);
        $invoice = $invoiceEntity->find($this->getRouteParameter('id'));
        $lines = $invoiceLineEntity->findByInvoice($invoice);

        if(is_null($invoice)) {
            return $this->redirectTo('/administration');
        }

        $file = $this->searchInvoiceFile($invoice->getId());

        return View::render('Admin/invoice', ['page' => 'invoices', 'user' => $user, 'invoice' => $invoice, 'lines' => $lines, 'externalFile' => $file]);
    }

    public function payAction() {
        Request::assertPostOnly();
        CSRF::validate();

        $userId = $this->logged();
        if(!$userId) {
            return $this->redirectTo('/administration/login');
        }

        $em = Entity::getEntityManager();
        $invoiceEntity = $em->getRepository(Invoice::class);
        $invoice = $invoiceEntity->find($this->getRouteParameter('id'));
        if(is_null($invoice)) {
            return $this->redirectTo('/administration');
        }

        $invoice->setStatus(true);
        $em->flush();

        if($invoice->getRecipient() == "drivncook") {
            return $this->redirectTo('/administration/invoices/received');
        }

        return $this->redirectTo('/administration/invoices/issued');
    }

    public function addAction() {
        Request::assertPostOnly();
        CSRF::validate();

        $userId = $this->logged();
        if(!$userId) {
            return $this->redirectTo('/administration/login');
        }

        $user = $this->getUser($userId);

        CSRF::generate();
        $em = Entity::getEntityManager();

        $invoiceRepository = $em->getRepository(Invoice::class);

        if(is_null($invoice = $invoiceRepository->find($this->getRouteParameter('id')))) {
            return $this->redirectTo('/administration');
        }

        $file = Request::getFile('invoice');

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if(!Validator::isValidFileExtension($extension) || !Validator::isSizeNotTooBig($file['size'])) {
            return $this->redirectTo('/administration/invoices/received');
        }

        $uploadDirectory = $this->getRealInvoicePath();
        $uploadFilePath = $uploadDirectory . $invoice->getId() . '.' . $extension;

        if(move_uploaded_file($file['tmp_name'], $uploadFilePath)) {
            return $this->redirectTo('/administration/invoices/' . $invoice->getId());
        }

        throw new \Exception("Impossible d'uploader le fichier, pressez le bouton arrière et recommencez.");
    }

    public function deleteRealInvoiceAction() {
        Request::assertPostOnly();
        CSRF::validate();

        $userId = $this->logged();
        if(!$userId) {
            return $this->redirectTo('/administration/login');
        }

        $user = $this->getUser($userId);

        CSRF::generate();
        $em = Entity::getEntityManager();

        $invoiceRepository = $em->getRepository(Invoice::class);
        if(is_null($invoice = $invoiceRepository->find($this->getRouteParameter('id')))) {
            return $this->redirectTo('/administration/invoices/received');
        }

        if($invoice->getRecipient() != "drivncook") {
            return $this->redirectTo('/administration/invoices/' . $invoice->getId());
        }

        $file = $this->searchInvoiceFile($invoice->getId());
        if($file == false) {
            return $this->redirectTo('/administration/invoices/' . $invoice->getId());
        }

        if(file_exists($file)) {
            unlink($file);
        }

        return $this->redirectTo('/administration/invoices/' . $invoice->getId());
    }

    public function realAction() {
        $userId = $this->logged();
        if(!$userId) {
            return $this->redirectTo('/administration/login');
        }

        $user = $this->getUser($userId);

        CSRF::generate();
        $em = Entity::getEntityManager();

        $invoiceRepository = $em->getRepository(Invoice::class);

        if(is_null($invoice = $invoiceRepository->find($this->getRouteParameter('id')))) {
            return $this->redirectTo('/administration');
        }

        if($invoice->getRecipient() != "drivncook") {
            return $this->redirectTo('/administration/invoices/' . $invoice->getId());
        }

        $file = $this->searchInvoiceFile($invoice->getId());

        if($file == false) {
            return $this->redirectTo('/administration/invoices/' . $invoice->getId());
        }

        $this->xStreamFile($file);
    }

    private function searchInvoiceFile($invoiceId) {
        $pattern = $this->getRealInvoicePath() . $invoiceId . '.';
        $filePattern = glob($pattern . '*');
        if(empty($filePattern)) {
            return false;
        }

        return $filePattern[0];
    }

    private function getRealInvoicePath() {
        return dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'Documents' . DIRECTORY_SEPARATOR . 'Invoices' . DIRECTORY_SEPARATOR;
    }
}