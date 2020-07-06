<?php

namespace App\Controllers\Home;

use App\Entity\Event;
use Core\Controller;
use Core\Request;
use Core\Entity;
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