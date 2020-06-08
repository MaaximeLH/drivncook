<?php

namespace App\Controllers\Admin;

use App\Entity\Admin;
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

    private $admin;
    private $em;
    private $eventRepository;
    private $eventUserRepository;


    public function __construct($routes)
    {
        parent::__construct($routes);
        $em = Entity::getEntityManager();

        $this->em = Entity::getEntityManager();
        $this->layout = 'Admin/assets/layout.phtml';

        $userId = Session::get('admin_id');
        if (is_null($admin = $em->find(Admin::class, $userId))) {
            return $this->redirectTo('/administration/login');
        }
        $this->eventRepository = $this->em->getRepository(Event::class);
        $this->eventUserRepository = $this->em->getRepository(EventUser::class);

        $this->admin = $admin;
    }

    public function indexAction()
    {
        $events = $this->eventRepository->findAll();
        CSRF::generate();
        $data['admin'] = $this->admin;
        $data['page'] = 'event';
        $data['events'] = $events;
        $this->load_view('Admin/event_listing', $data);
    }

    public function addAction()
    {
        if (!Request::isPost()) {
            echo 'non post';
            $this->show_404();
        }
        CSRF::validate();
        $params = Request::getAllParams();
        $event = new Event();
        if (Validator::isValidName($params['name'])) {
            $event->setName(trim($params['name']));
        }
        $event->setDescription(trim($params['description']));
        $date_begin_register_at = new \DateTime($params['date_begin_register_at']);
        $date_start = new \DateTime($params['date_start']);
        $date_end = new \DateTime($params['date_end']);
        if ($date_begin_register_at >= $date_start || $date_start >= $date_end) {
            echo 'Date Incorrect';
            die();
        }

        $event->setBeginRegisterAt($date_begin_register_at);
        $event->setStartAt($date_start);
        $event->setEndAt($date_end);
        $this->em->persist($event);
        $this->em->flush();
        echo '<div class="alert alert-success" role="alert">l\'event a bien été crée</div>';
        return;
    }

    public function editAction()
    {
        $event = $this->eventRepository->find($this->getRouteParameter('id'));
        if (Request::isPost()) {
            CSRF::validate();
            $params = Request::getAllParams();
            /**
             * TODO
             * set date
             */
            $event->setName($params['name']);
            $event->setDescription($params['description']);
        }
        CSRF::generate();

        $data['event'] = $event;
        $data['admin'] = $this->admin;
        $this->load_view('Admin/edit_event', $data);
    }

    public function deleteAction()
    {
        if (is_null($event = $this->eventRepository->find($this->getRouteParameter('id')))) {
            return $this->redirectTo("/administration/event");
        }
        $this->em->remove($event);
        $this->em->flush();
        return $this->redirectTo("/administration/event");
    }

    public function removeFranchise()
    {
        $franchise_id = $this->getRouteParameter('franchise_id');
        $event_id = $this->getRouteParameter('event_id');
        $eventUser = $this->eventUserRepository->findBy(['event_id' => $event_id, 'franchise_id' => $franchise_id]);
        $this->em->remove($eventUser);
        $this->em->flush();
        return $this->redirectTo("/administration/event/edit/" . $event_id);
    }
}
