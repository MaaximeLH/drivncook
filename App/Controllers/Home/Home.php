<?php

namespace App\Controllers\Home;

use App\Entity\Contact;
use App\Entity\Event;
use Core\Controller;
use Core\CSRF;
use Core\Request;
use Core\Entity;
use Core\Validator;
use Core\View;

class Home extends Controller {

    public function indexAction() {
        $em = Entity::getEntityManager();
        $eventRepository = $em->getRepository(Event::class);
        $events = $eventRepository->get3LastPublicEvent();
        return View::render('Home/index', ['page' => 'home', 'events' => $events]);
    }

    public function aboutAction() {
        return View::render('Home/about', ['page' => 'about']);
    }

    public function contactAction() {

        CSRF::generate();
        if(Request::isPost()) {
            CSRF::validate();
            $params = Request::getAllParams();

            $params['phone'] = Validator::isValidPhoneNumberAndFormatIt($params['phone']);

            if(
                empty($params['name']) ||
                (empty($params['email']) && empty($params['phone']))
                || empty($params['message'])
                || !Validator::isValidEmail($params['email'])
            ) {
                return View::render('Home/contact', ['page' => 'contact', 'error' => true, 'params' => $params]);
            }

            array_map('htmlspecialchars', $params);
            array_map('trim', $params);

            $em = Entity::getEntityManager();

            $contact = new Contact();
            $contact->setEmail($params['email']);
            $contact->setName($params['name']);
            $contact->setMessage($params['message']);
            $contact->setPhone($params['phone']);
            $contact->setIsRead(0);

            $em->persist($contact);
            $em->flush();

            return View::render('Home/contact', ['page' => 'contact', 'success' => true]);

        }

        return View::render('Home/contact', ['page' => 'contact']);
    }

    public function langAction() {
        $lang = $this->getRouteParameter('lang');
        $referer = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : "https://drivncook.store/";
        if(!in_array($lang, ['fr', 'en'])) {
            return $this->redirectTo($referer);
        }

        if($lang == "fr") $lang = "fr_FR";
        if($lang == "en") $lang = "en_US";

        $this->setLanguage($lang);

        $this->redirectTo($referer);
    }
}