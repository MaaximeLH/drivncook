<?php

namespace App\Controllers\Franchise;

use App\Entity\Users;
use App\Entity\Event;
use Core\Controller;
use Core\CSRF;
use Core\Entity;
use Core\Request;
use Core\Session;
use Core\Validator;

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
        $events = $this->eventRepository->findAll();
        CSRF::generate();
        $data['user'] = $this->user;
        $data['page'] = 'event';
        $data['events'] = $events;
        $this->load_view('Franchise/event_listing', $data);
    }
}
