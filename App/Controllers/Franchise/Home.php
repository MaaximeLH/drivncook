<?php

namespace App\Controllers\Franchise;

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

        return View::render('Franchise/index', ['user' => $user, 'page' => 'index', 'warehousesLocates' => json_encode($warehousesLocates)]);
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