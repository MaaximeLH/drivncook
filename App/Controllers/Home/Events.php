<?php

namespace App\Controllers\Home;

use Core\Controller;
use Core\Request;
use Core\View;
use Core\Session;
use Core\CSRF;
use Core\Entity;
use App\Entity\Event;
use App\Entity\EventCustomer;

class Events extends Controller {

    private $em;
    private $eventRepository;

    public function __construct($routes)
    {
        parent::__construct($routes);
        $this->em = Entity::getEntityManager();
        $this->eventRepository = $this->em->getRepository(Event::class);

    }

    public function indexAction() {
        $events = $this->eventRepository->findAll();
        return View::render('Home/events', ['page' => 'events', 'events' => $events]);
    }

    public function detailAction() {
        $eventId = $this->getRouteParameter('id');
        if (!$eventId) return $this->redirectTo('/events');

        $event = $this->eventRepository->find($eventId);
        return View::render('Home/eventDetail', ['page' => 'eventDetail', 'event' => $event]);
    }

    public function updateStatusAction() {
        $code = $this->getRouteParameter('code');
        $eventCustomerRepository = $this->em->getRepository(EventCustomer::class);
        $eventCustomer = $eventCustomerRepository->findOneBy(['code' => $code]);
        if (is_null($eventCustomer)) {
            $this->redirectTo('/');
        }
        $eventCustomer->setStatut('inscrit');
        $this->em->flush();

        Session::set('inscription_confirmed', \Core\Language::get('inscription_success'));
        $this->redirectTo('/customers');
    }
}