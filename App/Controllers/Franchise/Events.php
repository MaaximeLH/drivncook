<?php

namespace App\Controllers\Franchise;

use App\Entity\Users;
use App\Entity\Event;
use App\Entity\EventUser;
use Core\Controller;
use Core\CSRF;
use Core\Entity;
use Core\Request;
use Core\Session;
use Core\Validator;
use Core\View;

class Events extends Controller
{

    private $user;
    private $em;
    private $eventRepository;


    public function __construct($routes)
    {
        parent::__construct($routes);
        $em = Entity::getEntityManager();

        $this->em = Entity::getEntityManager();
        $this->layout = 'Franchise/assets/layout.phtml';

        $userId = Session::get('user_id');
        if (is_null($user = $em->find(Users::class, $userId))) {
            return $this->redirectTo('/administration/login');
        }
        $this->eventRepository = $this->em->getRepository(Event::class);

        $this->user = $user;
    }

    public function indexAction()
    {
        if(is_null($this->user->getTruck()) || !$this->user->getIsActive()) {
            return View::render('Franchise/noTruck', ['user' => $this->user]);
        }

        $events_inscrit = $this->eventRepository->getEventSubscribed($this->user->getId());
        $events = $this->eventRepository->findAll();
        CSRF::generate();
        $data['user'] = $this->user;
        $data['page'] = 'event';
        $data['events'] = $events;
        $data['breadcrumb'] = ['Event'];
        $data['events_inscrit'] = $events_inscrit;
        $this->load_view('Franchise/event_listing', $data);
    }

    public function subscribe()
    {
        if(is_null($this->user->getTruck()) || !$this->user->getIsActive()) {
            return View::render('Franchise/noTruck', ['user' => $this->user]);
        }

        $usersRepository = $this->em->getRepository(Users::class);
        $EventUserRepository = $this->em->getRepository(EventUser::class);

        if (is_null($user = $usersRepository->find($this->user->getId()))) {
            return $this->redirectTo('/administration/franchises');
        }
        if (is_null($event = $this->eventRepository->find($this->getRouteParameter('id')))) {
            return $this->redirectTo('/administration/franchises');
        }

        if ($EventUserRepository->findOneBy(['event' => $event, 'user' => $user]) != null) {
            return $this->redirectTo('/administration/franchises');
        }

        $eventUser = new EventUser();
        $eventUser->setEvent($event);
        $eventUser->setUser($user);

        $this->em->persist($eventUser);
        $this->em->flush();
        $this->redirectTo("/panel/event");
    }
}
