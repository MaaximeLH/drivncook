<?php

namespace App\Controllers\Home;

use Core\Controller;
use Core\Request;
use Core\View;
use Core\CSRF;
use Core\Entity;
use App\Entity\Event;

class Events extends Controller {

    public function __construct($routes)
    {
        parent::__construct($routes);
        $em = Entity::getEntityManager();
        $this->em = Entity::getEntityManager();
        $this->eventRepository = $this->em->getRepository(Event::class);

    }
    public function indexAction() {
        $events = $this->eventRepository->findAll();
        return View::render('Home/events', ['page' => 'events', 'events' => $events]);
    }
}