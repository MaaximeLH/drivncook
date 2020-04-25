<?php

namespace App\Controllers\Admin;

use App\Entity\Admin;
use App\Entity\Truck;
use App\Entity\Warehouse;
use Core\Controller;
use Core\CSRF;
use Core\Entity;
use Core\Session;
use Core\Request;
use Core\View;

/**
 * class Home, cette classe est utilisée pour la page d'accueil et le login / logout
 * @package App\Controllers\Admin
 */
class Home extends Controller {

    public function indexAction() {
        $em = Entity::getEntityManager();

        $admin = $em->find(Admin::class, Session::get('admin_id'));
        if(is_null($admin)) {
            return $this->redirectTo('/administration');
        }

        $truckRepository = $em->getRepository(Truck::class);
        $warehouseRepository = $em->getRepository(Warehouse::class);

        $trucks = $truckRepository->findAll();
        $trucksLocates = [];
        foreach ($trucks as $truck) {
            if(!empty($truck->getLongitude()) && !empty($truck->getLatitude())) {
                $trucksLocates[] = ['lon' => $truck->getLongitude(), 'lat' => $truck->getLatitude()];
            }
        }

        $warehouses = $warehouseRepository->findAll();
        $warehousesLocates = [];
        foreach ($warehouses as $warehouse) {
            if(!empty($warehouse->getLocation())) {
                $warehousesLocates[] = ['location' => str_replace("'", " ", $warehouse->getLocation())];
            }
        }

        return View::render('Admin/index', ['user' => $admin, 'page' => 'index', 'trucksLocates' => json_encode($trucksLocates), 'warehousesLocates' => json_encode($warehousesLocates)]);
    }

    public function loginAction() {
        if(Session::get('admin_id') !== false) {
            return $this->redirectTo('/administration/logout');
        }

        CSRF::generate();

        if(Request::isPost()) {
            CSRF::validate();
            $params = Request::getAllParams();

            $em = Entity::getEntityManager();
            $repository = $em->getRepository(Admin::class);

            $user = $repository->findOneByEmail($params['email']);

            if(is_null($user) || !password_verify($params['password'], $user->getPassword())) {
                return View::render('Admin/login', ['loginError' => true]);
            }

            Session::set('admin_id', $user->getId());
            $this->redirectTo('/administration');
        }

        return View::render('Admin/login');
    }

    public function logoutAction() {
        Session::destroy();
        $this->redirectTo('/administration/login');
    }
}