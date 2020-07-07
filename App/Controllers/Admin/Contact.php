<?php

namespace App\Controllers\Admin;

use App\Entity\Admin;
use Core\Controller;
use Core\Entity;
use Core\Session;
use Core\View;

class Contact extends Controller {

    private $admin;
    private $em;

    public function __construct($routes)
    {
        parent::__construct($routes);

        $userId = Session::get('admin_id');
        $this->em = Entity::getEntityManager();
        if (is_null($admin = $this->em->find(Admin::class, $userId))) {
            $this->redirectTo('/administration/login');
        }

        $this->admin = $admin;
    }

    public function unreadAction() {
        $contactRepository = $this->em->getRepository(\App\Entity\Contact::class);
        $contacts = $contactRepository->findByIsRead(0);

        return View::render('Admin/unread', ['page' => 'unread', 'admin' => $this->admin, 'contacts' => $contacts]);
    }

    public function readAction() {
        $contactRepository = $this->em->getRepository(\App\Entity\Contact::class);
        $contacts = $contactRepository->findByIsRead(1);

        return View::render('Admin/read', ['page' => 'read', 'admin' => $this->admin, 'contacts' => $contacts]);
    }

    public function updateAction() {
        $contactRepository = $this->em->getRepository(\App\Entity\Contact::class);
        $contact = $contactRepository->find($this->getRouteParameter('id'));

        $status = $this->getRouteParameter('status');
        $status = intval(trim($status));

        if(is_null($contact) || !in_array($status, [0,1])) {
            return $this->redirectTo('/administration/contact/unread');
        }

        $contact->setIsRead($status);
        $this->em->flush();

        // Si on passe un status à 1, c'est qu'on vient des messages non lus, alors on renvoie dessus
        if($status == 1) {
            return $this->redirectTo('/administration/contact/unread');
        }

        return $this->redirectTo('/administration/contact/read');
    }
}