<?php

namespace App\Controllers\Admin;

use App\Entity\Admin;
use App\Entity\Maintenance;
use App\Entity\MaintenanceInfo;
use App\Entity\Truck;
use App\Entity\Users;
use Core\Controller;
use Core\CSRF;
use Core\Entity;
use Core\Request;
use Core\Validator;
use Core\View;

/**
 * Class Trucks, gère les actions liées aux camions depuis l'administration
 * @package App\Controllers\Admin
 */
class Trucks extends Controller {

    private function getUser($id) {
        $em = Entity::getEntityManager();
        $repository = $em->getRepository(Admin::class);
        $user = $repository->find($id);
        return $user;
    }

    public function trucksAction() {
        $userId = $this->logged();
        if(!$userId) {
            return $this->redirectTo('/administration/login');
        }

        $user = $this->getUser($userId);

        $em = Entity::getEntityManager();
        $trucksRepository = $em->getRepository(Truck::class);
        $usersRepository = $em->getRepository(Users::class);
        $maintenanceRepository = $em->getRepository(Maintenance::class);

        $trucks = $trucksRepository->findAll();
        $availableFranchise = $usersRepository->findByTruck(null);

        foreach ($trucks as $key => $truck) {
            $trucks[$key]->user = $usersRepository->findOneByTruck($truck);
            $trucks[$key]->maintenance = $maintenanceRepository->findOneBy(['truck' => $truck, 'status' => 0]);
        }

        return View::render('Admin/trucks', ['user' => $user, 'page' => 'trucks', 'trucks' => $trucks, 'availableFranchise' => $availableFranchise]);
    }

    public function informationsAction() {
        $userId = $this->logged();
        if(!$userId) {
            return $this->redirectTo('/administration/login');
        }

        CSRF::generate();
        $user = $this->getUser($userId);

        $em = Entity::getEntityManager();
        $trucksRepository = $em->getRepository(Truck::class);
        $usersRepository = $em->getRepository(Users::class);

        if(is_null($truck = $trucksRepository->find($this->getRouteParameter('id')))) {
            return $this->redirectTo('/administration/trucks');
        }

        $truck->user = $usersRepository->findOneByTruck($truck);

        $maintenanceRepository = $em->getRepository(Maintenance::class);
        $maintenances = $maintenanceRepository->findByTruck($truck);
        $activeMaintenance = $maintenanceRepository->findOneBy(['truck' => $truck, 'status' => ['processing', 'waiting']]);

        return View::render('Admin/informationsTrucks', ['page' => 'trucks', 'user' => $user, 'truck' => $truck, 'maintenances' => $maintenances, 'activeMaintenance' => $activeMaintenance]);
    }

    public function maintenanceStatusAction() {
        Request::assertPostOnly();
        CSRF::validate();
        $userId = $this->logged();
        if(!$userId) {
            return $this->redirectTo('/administration/login');
        }

        $em = Entity::getEntityManager();
        $user = $this->getUser($userId);

        $truckRepository = $em->getRepository(Truck::class);
        $maintenanceRepository = $em->getRepository(Maintenance::class);

        if(is_null($truck = $truckRepository->find($this->getRouteParameter('id')))) {
            return $this->redirectTo('/administration/trucks');
        }

        if(is_null($maintenance = $maintenanceRepository->find($this->getRouteParameter('mid')))) {
            return $this->redirectTo('/administration/trucks/' . $truck->getId() . '/informations');
        }

        $params = Request::getAllParams();
        if(!in_array($params['status'], ['waiting', 'processing', 'finished'])) {
            return $this->redirectTo('/administration/trucks/' . $truck->getId() . '/informations');
        }

        if($maintenance->getStatus() == $params['status']) {
            return $this->redirectTo('/administration/trucks/' . $truck->getId() . '/maintenance/' . $maintenance->getId());
        }

        $previousStatus = $maintenance->getStatus();
        $maintenance->setStatus(trim($params['status']));
        $em->flush();

        $maintenanceInfo = new MaintenanceInfo();
        $maintenanceInfo->setMaintenance($maintenance);
        $maintenanceInfo->setInfo("Statut passé de '" . $previousStatus . "' à '" . $maintenance->getStatus() . "' par " . $user->getFirstname() . " " . $user->getLastname());

        $em->persist($maintenanceInfo);
        $em->flush();

        return $this->redirectTo('/administration/trucks/' . $truck->getId() . '/maintenance/' . $maintenance->getId());
    }

    public function setMaintenanceAction() {
        Request::assertPostOnly();
        CSRF::validate();
        $userId = $this->logged();
        if(!$userId) {
            return $this->redirectTo('/administration/login');
        }

        $user = $this->getUser($userId);

        $em = Entity::getEntityManager();
        $trucksRepository = $em->getRepository(Truck::class);
        $usersRepository = $em->getRepository(Users::class);

        if(is_null($truck = $trucksRepository->find($this->getRouteParameter('id')))) {
            return $this->redirectTo('/administration/trucks');
        }

        $maintenanceRepository = $em->getRepository(Maintenance::class);
        $activeMaintenance = $maintenanceRepository->findOneBy(['truck' => $truck, 'status' => ['processing', 'waiting']]);

        // Si pas de maintenance active
        if(!is_null($activeMaintenance)) {
            return $this->redirectTo('/administration/trucks/' . $truck->getId() . '/informations');
        }

        $maintenance = new Maintenance();
        $maintenance->setStatus('waiting');
        $maintenance->setTruck($truck);

        $em->persist($maintenance);
        $em->flush();

        $maintenanceInfo = new MaintenanceInfo();
        $maintenanceInfo->setMaintenance($maintenance);
        $maintenanceInfo->setInfo("Mise en maintenance du camion par " . $user->getFirstname() . ' ' . $user->getLastname());

        $em->persist($maintenanceInfo);
        $em->flush();

        return $this->redirectTo('/administration/trucks/' . $truck->getId() . '/informations');
    }

    public function maintenanceDetailsAction() {
        $userId = $this->logged();
        if(!$userId) {
            return $this->redirectTo('/administration/login');
        }

        $user = $this->getUser($userId);
        $em = Entity::getEntityManager();

        CSRF::generate();
        $truckRepository = $em->getRepository(Truck::class);
        $maintenanceRepository = $em->getRepository(Maintenance::class);
        $maintenanceInfoRepository = $em->getRepository(MaintenanceInfo::class);

        if(is_null($truck = $truckRepository->find($this->getRouteParameter('id')))) {
            return $this->redirectTo('/administration/trucks');
        }

        if(is_null($maintenance = $maintenanceRepository->find($this->getRouteParameter('mid')))) {
            return $this->redirectTo('/administration/trucks/' . $truck->getId() . '/informations');
        }

        $data = $maintenanceInfoRepository->findByMaintenance($maintenance);
        $data = array_reverse($data);

        $infos = [];
        foreach ($data as $item) {
            $infos[$item->getCreatedAt()->format('d/m/Y')][] = $item;
        }

        if(Request::isPost()) {
            CSRF::validate();
            $params = Request::getAllParams();

            $maintenanceInfo = new MaintenanceInfo();
            $maintenanceInfo->setMaintenance($maintenance);
            $maintenanceInfo->setInfo(htmlspecialchars(trim($params['info'])));

            $em->persist($maintenanceInfo);
            $em->flush();

            return $this->redirectTo('/administration/trucks/' . $truck->getId() . '/maintenance/' . $maintenance->getId());
        }

        return View::render('Admin/maintenanceDetails', ['page' => 'trucks', 'user' => $user, 'truck' => $truck, 'maintenance' => $maintenance, 'infos' => $infos]);
    }

    public function addTrucksAction() {
        $userId = $this->logged();
        if(!$userId) {
            return $this->redirectTo('/administration/login');
        }

        $user = $this->getUser($userId);
        $em = Entity::getEntityManager();
        $usersRepository = $em->getRepository(Users::class);
        $availableFranchise = $usersRepository->findByTruck(null);

        CSRF::generate();
        if(Request::isPost()){
            CSRF::validate();
            $params = Request::getAllParams();
            $fields = [];

            if(!Validator::isValidLicensePlate($params['matriculation'])) {
                $fields['matriculation'] = htmlspecialchars(trim($params['matriculation']));
            }

            if(isset($params['assign'])) {
                $toAssign = $usersRepository->find($params['assign']);
            } else {
                $toAssign = null;
            }

            $toAssign = null;
            if(!empty($params['assign'])) {
                $toAssign = $usersRepository->find($params['assign']);
                if(is_null($toAssign) || $toAssign->getTruck() !== NULL) {
                    $fields['assign'] = null;
                }
            }

            if(!empty($fields)) {
                return View::render('Admin/addTrucks', ['user' => $user, 'page' => 'trucks_add', 'availableFranchise' => $availableFranchise, 'fields' => $fields]);
            }

            $truck = new Truck();
            $truck->setLatitude(null);
            $truck->setLatitude(null);
            $truck->setMatriculation(trim($params['matriculation']));


            $em->persist($truck);
            $em->flush();

            if(!is_null($toAssign)) {
                $toAssign->setTruck($truck);
                $em->flush();
            }

            return $this->redirectTo('/administration/trucks/' . $truck->getId() . '/informations');
        }

        return View::render('Admin/addTrucks', ['user' => $user, 'page' => 'trucks_add', 'availableFranchise' => $availableFranchise]);
    }

    public function assignAction() {
        Request::assertPostOnly();
        $userId = $this->logged();
        if(!$userId) {
            return $this->redirectTo('/administration/login');
        }


        CSRF::validate();
        $em = Entity::getEntityManager();

        $usersRepository = $em->getRepository(Users::class);
        $trucksRepository = $em->getRepository(Truck::class);

        $params = Request::getAllParams();
        $truckId = $this->getRouteParameter('id');

        if(!isset($params['assign'])) {
            return $this->redirectTo('/administration/trucks');
        }

        $assignId = intval($params['assign']);
        $truck = $trucksRepository->find($truckId);
        $toAssign = $usersRepository->find($assignId);

        if(is_null($truck) || is_null($toAssign) || !$truck->getActive()) {
            return $this->redirectTo('/administration/trucks');
        }

        $toAssign->setTruck($truck);
        $em->flush();

        return $this->redirectTo('/administration/trucks');
    }

    public function removeAssignAction() {
        Request::assertPostOnly();
        $userId = $this->logged();
        if(!$userId) {
            return $this->redirectTo('/administration/login');
        }

        CSRF::validate();
        $em = Entity::getEntityManager();

        $usersRepository = $em->getRepository(Users::class);
        $trucksRepository = $em->getRepository(Truck::class);
        $truckId = $this->getRouteParameter('id');
        $truck = $trucksRepository->find($truckId);

        $truckUser = $usersRepository->findOneByTruck($truck);

        if(is_null($truck) && is_null($truckUser)) {
            return $this->redirectTo('/administration/trucks');
        }

        $truckUser->setTruck(null);
        $em->flush();

        return $this->redirectTo('/administration/trucks');
    }

    public function setActiveAction() {
        Request::assertPostOnly();
        $userId = $this->logged();
        if(!$userId) {
            return $this->redirectTo('/administration/login');
        }

        CSRF::validate();
        $em = Entity::getEntityManager();

        $trucksRepository = $em->getRepository(Truck::class);
        $truckId = $this->getRouteParameter('id');
        $truck = $trucksRepository->find($truckId);

        if(is_null($truck) || $truck->getActive()) {
            return $this->redirectTo('/administration/trucks');
        }

        $truck->setActive(1);
        $em->flush();

        return $this->redirectTo('/administration/trucks');
    }

    public function setInactiveAction() {
        Request::assertPostOnly();
        $userId = $this->logged();
        if(!$userId) {
            return $this->redirectTo('/administration/login');
        }

        CSRF::validate();
        $em = Entity::getEntityManager();

        $usersRepository = $em->getRepository(Users::class);
        $trucksRepository = $em->getRepository(Truck::class);
        $truckId = $this->getRouteParameter('id');
        $truck = $trucksRepository->find($truckId);

        $truckUser = $usersRepository->findOneByTruck($truck);

        if(is_null($truck) || !is_null($truckUser) || !$truck->getActive()) {
            return $this->redirectTo('/administration/trucks');
        }

        $truck->setActive(0);
        $em->flush();

        return $this->redirectTo('/administration/trucks');
    }
}