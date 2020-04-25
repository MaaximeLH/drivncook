<?php

namespace App\Controllers\Admin;

use App\Entity\Admin;
use App\Entity\Truck;
use App\Entity\Users;
use Core\Controller;
use Core\CSRF;
use Core\Entity;
use Core\Request;
use Core\Session;
use Core\Validator;
use Core\View;

class Franchises extends Controller {

    private $admin;

    public function __construct($routes)
    {
        parent::__construct($routes);

        $userId = Session::get('admin_id');
        $em = Entity::getEntityManager();
        if(is_null($admin = $em->find(Admin::class, $userId))) {
            return $this->redirectTo('/administration/login');
        }

        $this->admin = $admin;
    }


    public function franchisesAction() {
        $em = Entity::getEntityManager();
        $usersRepository = $em->getRepository(Users::class);
        $users = $usersRepository->findAll();

        CSRF::generate();

        return View::render('Admin/franchises', ['user' => $this->admin, 'page' => 'franchises', 'users' => $users]);
    }

    public function editAction() {
        $em = Entity::getEntityManager();

        $trucksRepository = $em->getRepository(Truck::class);
        $usersRepository = $em->getRepository(Users::class);

        if(is_null($user = $usersRepository->find($this->getRouteParameter('id')))) {
            return $this->redirectTo('/administration/franchises');
        }

        $trucks = $trucksRepository->findByActive(1);

        foreach ($trucks as $key => $truck) {

            // Si le franchisé a un camion et que son camion est celui qu'on itère, alors on skip
            if(!is_null($user->getTruck()) && $user->getTruck()->getId() == $truck->getId()) continue;

            // Si le truck qu'on itère est déjà assigné à un utilisateur
            if(!is_null($usersRepository->findOneByTruck($truck))) {
                unset($trucks[$key]);
            }
        }

        CSRF::generate();
        if(Request::isPost()) {
            CSRF::validate();
            $params = Request::getAllParams();
            $fields = [];

            if($user->getEmail() != $params['email']) {
                if(Validator::isValidEmail($params['email']) && !$usersRepository->findOneByEmail($params['email'])) {
                    $user->setEmail(htmlspecialchars(trim($params['email'])));
                    $fields['email'] = true;
                } else {
                    $fields['email'] = false;
                }
            }

            if($user->getFirstname() != $params['firstname']) {
                if(Validator::isValidName($params['firstname'])) {
                    $user->setFirstname(htmlspecialchars(trim($params['firstname'])));
                    $fields['firstname'] = true;
                } else {
                    $fields['firstname'] = false;
                }
            }

            if($user->getLastname() != $params['lastname']) {
                if(Validator::isValidName($params['lastname'])) {
                    $user->setLastname(htmlspecialchars(trim($params['lastname'])));
                    $fields['lastname'] = true;
                } else {
                    $fields['lastname'] = false;
                }
            }

            if($user->getSocietyName() != $params['society']) {
                if(Validator::isValidName($params['society'])) {
                    $user->setSocietyName(htmlspecialchars(trim($params['society'])));
                    $fields['society'] = true;
                } else {
                    $fields['society'] = false;
                }
            }

            if($user->getSiret() != $params['siret']) {
                if(Validator::isValidSiret($params['siret'])) {
                    $user->setSiret(htmlspecialchars(trim($params['siret'])));
                    $fields['siret'] = true;
                } else {
                    $fields['siret'] = false;
                }
            }

            $phone = Validator::isValidPhoneNumberAndFormatIt($params['phone_number']);
            if($user->getPhoneNumber() != $phone) {
                if(!is_null($phone) && $phone !== false) {
                    $user->setPhoneNumber($phone);
                    $fields['phone_number'] = true;
                } else {
                    $fields['phone_number'] = true;
                }
            }

            if($user->getAddressLine() != $params['address_line']) {
                if(strlen($params['address_line']) >= 3) {
                    $user->setAddressLine(htmlspecialchars(trim($params['address_line'])));
                    $fields['address_line'] = true;
                } else {
                    $fields['address_line'] = false;
                }
            }

            if($user->getAddressCity() != $params['city']) {
                if(Validator::isValidCityName($params['city'])) {
                    $user->setAddressCity(htmlspecialchars(trim($params['city'])));
                    $fields['city'] = true;
                } else {
                    $fields['city'] = false;
                }
            }

            if($user->getPostalCode() != $params['postal_code']) {
                if(Validator::isValidPostalCode($params['postal_code'])) {
                    $user->setPostalCode(htmlspecialchars(trim($params['postal_code'])));
                    $fields['postal_code'] = true;
                } else {
                    $fields['postal_code'] = false;
                }
            }

            if($user->getAddressState() != $params['state']) {
                if(strlen($params['state']) >= 3) {
                    $user->setAddressState(htmlspecialchars(trim($params['state'])));
                    $fields['state'] = true;
                } else {
                    $fields['state'] = false;
                }
            }

            if($user->getCountry() != $params['country']) {
                if(strlen($params['country']) >= 3) {
                    $user->setCountry(htmlspecialchars(trim($params['country'])));
                    $fields['country'] = true;
                } else {
                    $fields['country'] = false;
                }
            }

            if(!empty($params['password']) && !empty($params['passwordConfirm'])) {
                if($params['password'] == $params['passwordConfirm'] && Validator::isGoodPassword($params['password'])) {
                    $user->setPassword(password_hash($params['password'], PASSWORD_DEFAULT));
                    $fields['password'] = true;
                } else {
                    $fields['password'] = false;
                }
            }

            if((!empty($params['password']) && empty($params['passwordConfirm'])) || (empty($params['password']) && !empty($params['passwordConfirm']))) {
                $fields['password'] = false;
            }

            $assignTruckId = 0;
            if(!empty($params['assign'])) {
                $assignTruckId = intval($params['assign']);
            }

            $assignTruck = $trucksRepository->find($assignTruckId);
            $checkForAlreadyAssignTruck = $usersRepository->findOneByTruck($assignTruck);

            if($assignTruckId == 0) {
                $user->setTruck(null);
            } else {

                // Si le camion existe bien, et n'est pas déjà assigné
                if(!is_null($assignTruck) && is_null($checkForAlreadyAssignTruck)) {

                    // Si j'ai un camion, et qu'il ne m'est pas déjà assigné OU que je n'ai pas de camion
                    if(
                        (!is_null($user->getTruck()) && $user->getTruck()->getId() != $assignTruck->getId()) ||
                        (is_null($user->getTruck()))
                    ) {
                        $user->setTruck($assignTruck);
                    }
                }
            }

            $em->flush();
            return View::render('Admin/editFranchises', ['user' => $this->admin, 'page' => 'franchises', 'franchise' => $user, 'trucks' => $trucks, 'fields' => $fields]);
        }

        return View::render('Admin/editFranchises', ['user' => $this->admin, 'page' => 'franchises', 'franchise' => $user, 'trucks' => $trucks]);
    }

    public function addFranchisesAction() {
        $em = Entity::getEntityManager();

        $trucksRepository = $em->getRepository(Truck::class);
        $usersRepository = $em->getRepository(Users::class);

        $trucks = $trucksRepository->findByActive(1);
        foreach ($trucks as $key => $truck) {
            if(!is_null($usersRepository->findOneByTruck($truck))) {
                unset($trucks[$key]);
            }
        }

        CSRF::generate();

        if(Request::isPost()) {
            CSRF::validate();
            $params = Request::getAllParams();
            $fields = [];

            if(empty($params['email']) || !Validator::isValidEmail($params['email']) || !is_null($usersRepository->findOneByEmail($params['email']))) {
                $fields['email'] = htmlspecialchars(trim($params['email']));
            }

            foreach (['firstname', 'lastname', 'society'] as $key) {
                if(empty($params[$key]) || !Validator::isValidName($params[$key])) {
                    $fields[$key] = htmlspecialchars(trim($params[$key]));
                }
            }

            if(empty($params['siret']) || !Validator::isValidSiret($params['siret'])) {
                $fields['siret'] = htmlspecialchars(trim($params['siret']));
            }

            if(empty($params['password']) || empty($params['passwordConfirm']) || $params['password'] != $params['passwordConfirm'] || !Validator::isGoodPassword($params['password'])){
                $fields['password'] = htmlspecialchars($params['password']);
            }

            $phone = Validator::isValidPhoneNumberAndFormatIt($params['phone_number']);
            if(empty($params['phone_number']) || $phone == false || $phone = null) {
                $fields['phone_number'] = htmlspecialchars($params['phone_number']);
            }

            if(!empty($params['address_line'])) {
                $addressLine = htmlspecialchars(trim($params['address_line']));
            } else {
                $addressLine = null;
            }

            if(!empty($params['city']) && Validator::isValidCityName($params['city'])) {
                $city = htmlspecialchars(trim($params['city']));
            } else {
                $city = null;
            }

            if(!empty($params['postal_code']) && Validator::isValidPostalCode($params['postal_code'])) {
                $postalCode = htmlspecialchars(trim($params['postal_code']));
            } else {
                $postalCode = null;
            }

            if(!empty($params['state'])) {
                $state = htmlspecialchars(trim($params['state']));
            } else {
                $state = null;
            }

            if(!empty($params['country'])) {
                $country = htmlspecialchars(trim($params['country']));
            } else {
                $country = null;
            }

            if(!empty($fields)) {
                return View::render('Admin/addFranchises', ['user' => $this->admin, 'page' => 'add_franchises', 'trucks' => $trucks, 'fields' => $fields, 'params' => $params]);
            }

            $users = new Users();
            $users->setLastName(htmlspecialchars(trim($params['lastname'])));
            $users->setFirstName(htmlspecialchars(trim($params['firstname'])));
            $users->setSocietyName(htmlspecialchars(trim($params['society'])));
            $users->setSiret(htmlspecialchars(trim($params['siret'])));
            $users->setEmail(htmlspecialchars(trim($params['email'])));
            $users->setPassword(password_hash($params['password'], PASSWORD_DEFAULT));
            $users->setPhoneNumber($phone);
            $users->setAddressLine($addressLine);
            $users->setPostalCode($postalCode);
            $users->setAddressCity($city);
            $users->setAddressState($state);
            $users->setCountry($country);
            $users->setEvent(null);
            $users->setTruck($trucksRepository->findOneById(intval(trim($params['assign']))));

            $em->persist($users);
            $em->flush();

            return $this->redirectTo('/administration/franchises');
        }

        return View::render('Admin/addFranchises', ['user' => $this->admin, 'page' => 'add_franchises', 'trucks' => $trucks]);
    }

    public function blockAction() {
        Request::assertPostOnly();
        CSRF::validate();

        $em = Entity::getEntityManager();

        $usersRepository = $em->getRepository(Users::class);

        if(is_null($user = $usersRepository->find($this->getRouteParameter('id')))) {
            return $this->redirectTo('/administration/franchises');
        }

        $user->setIsActive(($user->getIsActive()) ? false : true);
        $em->flush();

        return $this->redirectTo('/administration/franchises');
    }

    public function deleteAction() {
        Request::assertPostOnly();
        CSRF::validate();

        $em = Entity::getEntityManager();
        $usersRepository = $em->getRepository(Users::class);

        if(is_null($user = $usersRepository->find($this->getRouteParameter('id')))) {
            return $this->redirectTo('/administration/franchises');
        }

        die('TODO, A FAIRE QUAND TOUT SERA TERMINEE');
    }
}