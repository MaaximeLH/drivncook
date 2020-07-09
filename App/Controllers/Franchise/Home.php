<?php

namespace App\Controllers\Franchise;

use App\Entity\Openings;
use App\Entity\Users;
use App\Entity\Warehouse;
use Core\Controller;
use Core\CSRF;
use Core\Entity;
use Core\Request;
use Core\Session;
use Core\Validator;
use Core\View;

/**
 * class Home, cette classe est utilisée pour la page d'accueil et le login / logout
 * @package App\Controllers\Franchise
 */
class Home extends Controller
{

    public function indexAction() {
        $em = Entity::getEntityManager();

        $user = $em->find(Users::class, Session::get('user_id'));
        if(is_null($user)) {
            return $this->redirectTo('/panel/login');
        }

        $warehouseRepository = $em->getRepository(Warehouse::class);
        $warehouses = $warehouseRepository->findAll();

        $warehousesLocates = [];
        foreach ($warehouses as $warehouse) {
            if(!empty($warehouse->getLocation())) {
                $warehousesLocates[] = ['location' => str_replace("'", " ", $warehouse->getLocation())];
            }
        }

        $openingsRepository = $em->getRepository(Openings::class);
        $openings = $openingsRepository->findOneByUser($user);
        if(is_null($openings)) {

            $date = new \DateTime();
            $open = $date->setTime(0, 0);
            $date = new \DateTime();
            $close = $date->setTime(23, 59);

            $opening = new Openings();
            $opening->setUser($user);

            $opening->setMondayOpen($open);
            $opening->setTuesdayOpen($open);
            $opening->setWedOpen($open);
            $opening->setThursdayOpen($open);
            $opening->setFridayOpen($open);
            $opening->setSatOpen($open);
            $opening->setSunOpen($open);

            $opening->setMondayClose($close);
            $opening->setTuesdayClose($close);
            $opening->setWedClose($close);
            $opening->setThursdayClose($close);
            $opening->setFridayClose($close);
            $opening->setSatClose($close);
            $opening->setSunClose($close);

            $opening->setGlobalOpen(1);

            $em->persist($opening);
            $em->flush();

            $openings = $openingsRepository->findOneByUser($user);
        }

        $update = false;
        if(Request::isPost()) {
            $params = Request::getAllParams();

            foreach ($params as $key => $value) {
                if($key != "global_open") {

                    $time = new \DateTime();
                    $tmp = explode(" ", str_replace(":", " ", $value));
                    $params[$key] = $time->setTime($tmp[0], $tmp[1]);

                }
            }

            if($params['monday_open'] < $params['monday_close']) {
                $openings->setMondayOpen($params['monday_open']);
                $openings->setMondayClose($params['monday_close']);
            }

            if($params['tuesday_open'] < $params['tuesday_close']) {
                $openings->setTuesdayOpen($params['tuesday_open']);
                $openings->setTuesdayClose($params['tuesday_close']);
            }

            if($params['wed_open'] < $params['wed_close']) {
                $openings->setWedOpen($params['wed_open']);
                $openings->setWedClose($params['wed_close']);
            }

            if($params['thursday_open'] < $params['thursday_close']) {
                $openings->setThursdayOpen($params['thursday_open']);
                $openings->setThursdayClose($params['thursday_close']);
            }

            if($params['friday_open'] < $params['friday_close']) {
                $openings->setFridayOpen($params['friday_open']);
                $openings->setFridayClose($params['friday_close']);
            }

            if($params['sat_open'] < $params['sat_close']) {
                $openings->setSatOpen($params['sat_open']);
                $openings->setSatClose($params['sat_close']);
            }

            if($params['sun_open'] < $params['sun_close']) {
                $openings->setSunOpen($params['sun_open']);
                $openings->setSunClose($params['sun_close']);
            }

            if(isset($params['global_open'])) {
                $openings->setGlobalOpen(0);
            } else {
                $openings->setGlobalOpen(1);
            }

            $update = true;
            $em->flush();
        }

        return View::render('Franchise/index', ['user' => $user, 'page' => 'index', 'warehousesLocates' => json_encode($warehousesLocates), 'openings' => $openings, 'update' => $update]);
    }

    public function loginAction() {
        if(Session::get('user_id') !== false) {
            return $this->redirectTo('/panel/logout');
        }

        CSRF::generate();

        if(Request::isPost()) {
            CSRF::validate();
            $params = Request::getAllParams();

            $em = Entity::getEntityManager();
            $repository = $em->getRepository(Users::class);

            $user = $repository->findOneByEmail($params['email']);

            if(is_null($user) || !password_verify($params['password'], $user->getPassword())) {
                return View::render('Franchise/login', ['loginError' => true]);
            }

            Session::set('user_id', $user->getId());
            $this->redirectTo('/panel');
        }

        return View::render('Franchise/login');
    }

    public function logoutAction() {
        Session::destroy();
        $this->redirectTo('/panel/login');
    }
}