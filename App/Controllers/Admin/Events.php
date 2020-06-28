<?php

namespace App\Controllers\Admin;

use App\Entity\Admin;
use App\Entity\Event;
use App\Entity\EventUser;
use App\Entity\EventCustomer;
use App\Entity\Users;
use App\Entity\Customer;
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

        $this->em = $em;
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
        $eventCustomerRepository = $this->em->getRepository(EventCustomer::class);
        $eventCustomers = $eventCustomerRepository->findBy(['event' => $event]);
        if (Request::isPost()) {
            CSRF::validate();
            $params = Request::getAllParams();
            $event->setName($params['name']);
            $event->setDescription($params['description']);

            $event->setStartAt(\DateTime::createFromFormat('Y-m-d H:i', $params['date_start'] . ' ' . $params['time_start']));
            $event->setEndAt(\DateTime::createFromFormat('Y-m-d H:i', $params['date_end'] . ' ' . $params['time_end']));
            $event->setBeginRegisterAt(\DateTime::createFromFormat('Y-m-d H:i', $params['date_begin_register_at'] . ' ' . $params['time_begin_register_at']));
            
            if (in_array($params['type'], ['invite-only', 'public', 'with-VIP'])) {
                $event->setType($params['type']);
            }
            $this->em->flush();
        }
        CSRF::generate();

        $data['eventCustomers'] = $eventCustomers;
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
        $userRepository = $this->em->getRepository(Users::class);


        $franchise_id = $this->getRouteParameter('franchise');
        $event_id = $this->getRouteParameter('event');
        $event = $this->eventRepository->find($event_id);
        $user = $userRepository->find($franchise_id);
        $eventUser = $this->eventUserRepository->findOneBy(['event' => $event, 'user' => $user]);
        $this->em->remove($eventUser);
        $this->em->flush();
        return $this->redirectTo("/administration/event/edit/" . $event_id);
    }

    public function previewInvitationAction()
    {
        $this->layout = 'empty_layout.phtml';

        $customerRepository = $this->em->getRepository(Customer::class);
        $event = $this->eventRepository->find($this->getRouteParameter('event'));
        $cusotmer = $customerRepository->find($this->getRouteParameter('customer'));

        $data['event'] = $event;
        $data['cusotmer'] = $cusotmer;
        $this->load_view('Admin/event/invitation', $data);
    }

    public function postCMS()
    {
        $event_id = $this->getRouteParameter('id');
        $event = $this->eventRepository->find($event_id);
        if (Request::isPost()) {
            CSRF::validate();
            $error = [];
            $params = Request::getAllParams();
            if (empty($params['titleEmailFR'])) {
                $error[] = \Core\Language::get('titleEmailFR');
            } else {
                $event->setTitleEmailFR(trim($params['titleEmailFR']));
            }
            if (empty($params['textEmailFR'])) {
                $error[] = \Core\Language::get('textEmailFR');
            } else {
                $event->setTextEmailFR(trim($params['textEmailFR']));
            }
            if (empty($params['wysiwygFR'])) {
                $error[] = \Core\Language::get('wysiwygFR');
            } else {
                $event->setTextFR(trim($params['wysiwygFR']));
            }
            if (empty($params['titleEmailEN'])) {
                $error[] = \Core\Language::get('titleEmailEN');
            } else {
                $event->setTitleEmailEN(trim($params['titleEmailEN']));
            }
            if (empty($params['textEmailEN'])) {
                $error[] = \Core\Language::get('textEmailEN');
            } else {
                $event->setTextEmailEN(trim($params['textEmailEN']));
            }
            if (empty($params['wysiwygEN'])) {
                $error[] = \Core\Language::get('wysiwygEN');
            } else {
                $event->setTextEN(trim($params['wysiwygEN']));
            }

            $file = Request::getFile('img');
            if (!empty($file['tmp_name'])) {
                $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (!Validator::isValidFileExtension($extension)) {
                    $error[] = \Core\Language::get('errorImage');
                    return $this->redirectTo('/administration/event/edit/' . $event_id);
                }
                $uploadDirectory = $this->getImgUploadPath($event_id);
                $name = basename($file["name"]);
                if (!move_uploaded_file($file['tmp_name'], "$uploadDirectory/$name")) {
                    $error[] = \Core\Language::get('errorImage');
                } else {
                    $event->setImage($name);
                }
            }

            if (!empty($error)) {
                $data['error'] = $error;
                Session::set('errorCMS', $error);
                return $this->redirectTo('/administration/event/edit/' . $event_id);
            }

            $this->em->flush();
        }
        return $this->redirectTo('/administration/event/edit/' . $event_id);
    }

    private function getImgUploadPath(int $event_id)
    {
        $path = dirname(__DIR__, 3) . '/public/dist/uploads/event/' . $event_id;
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        return $path;
    }

    public function sendInvitation()
    {
        $event_id = $this->getRouteParameter('id');
        if (!$event_id) {
            $this->redirectTo('/administration/event/edit/' . $event_id);
        }
        $event = $this->eventRepository->find($event_id);
        $customerRepository = $this->em->getRepository(Customer::class);

        if (Request::isPost()) {
            CSRF::validate();
            $params = Request::getAllParams();
            var_dump($params);
            foreach ($params['id'] as $id) {
                $customer = $customerRepository->find($id);

                $eventCustomer = new eventCustomer();
                
                $eventCustomer->setEvent($event);
                $eventCustomer->setCustomer($customer);
                $eventCustomer->setStatut('Invité');
                $this->em->persist($eventCustomer);
                $this->em->flush();
                $this->sendInvitaitonEmail($event, $customer);

            }
            $this->redirectTo('/administration/event/edit/' . $event_id);
        }
    }

    public function resendInvitation()
    {
        $event_id = $this->getRouteParameter('event');
        $customer_id = $this->getRouteParameter('customer');
        if (!$event_id || !$customer_id) {
            $this->redirectTo('/administration/event/edit/' . $event_id);
        }
        $customerRepository = $this->em->getRepository(Customer::class);
        $event = $this->eventRepository->find($event_id);
        $customer = $customerRepository->find($customer_id);

        $this->sendInvitaitonEmail($event, $customer);
        $this->redirectTo('/administration/event/edit/' . $event_id);
    }

    private function sendInvitaitonEmail($event, $customer)
    {
        ob_start();
                require(VIEWPATH . 'Admin/event/invitation.phtml');
        $message = ob_get_clean();

        $from = "norepley@drivncook.store";
        $to = $customer->getEmail();
        $header = 'Content-Type: text/html';
        if (mail($from, $to, $message, $header)) {
            die("Email sent");
        }
    }
}
