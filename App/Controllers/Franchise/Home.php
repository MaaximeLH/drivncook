<?php

namespace App\Controllers\Franchise;

use App\Entity\Users;
use App\Entity\Warehouse;
use Core\Controller;
use Core\CSRF;
use Core\Entity;
use Core\Request;
use Core\Session;
use Core\Stripe;
use Core\Validator;
use Core\View;

/**
 * class Home, cette classe est utilisée pour la page d'accueil et le login / logout / mon compte & paiements en ligne
 * @package App\Controllers\Franchise
 */
class Home extends Controller
{
    protected function logged() {
        return Session::get('user_id');
    }

    public function indexAction() {
        $userId = $this->logged();
        if(!$userId) {
            return $this->redirectTo('/panel/login');
        }

        $em = Entity::getEntityManager();
        $user = $em->find(Users::class, $userId);
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
        $userId = $this->logged();

        if($userId !== false) {
            $this->redirectTo('/panel');
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

    public function myAccountAction() {
        $userId = $this->logged();
        if(!$userId) {
            return $this->redirectTo('/panel/login');
        }

        $em = Entity::getEntityManager();
        $usersRepository = $em->getRepository(Users::class);
        $user = $usersRepository->find($userId);

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

            $em->flush();
            return View::render('Franchise/myAccount', ['user' => $user, 'page' => 'my_account', 'fields' => $fields]);
        }

        return View::render('Franchise/myAccount', ['user' => $user, 'page' => 'my_account']);
    }

    public function onlinePaymentAction() {
        $userId = $this->logged();
        if(!$userId) {
            return $this->redirectTo('/panel/login');
        }

        $em = Entity::getEntityManager();
        $usersRepository = $em->getRepository(Users::class);
        $user = $usersRepository->find($userId);

        CSRF::generate();
        if(Request::isPost()) {
            CSRF::validate();
            $params = Request::getAllParams();
            $fields = [];

            if($user->getStripePublicKey() != $params['stripe_public_key']) {
                if(empty($params['stripe_public_key']) || strlen($params['stripe_public_key']) >= 5) {
                    $user->setStripePublicKey(htmlspecialchars(trim($params['stripe_public_key'])));
                    $fields['stripe_public_key'] = true;
                } else {
                    $fields['stripe_public_key'] = false;
                }
            }

            if($user->getStripePrivateKey() != $params['stripe_private_key']) {
                if(empty($params['stripe_private_key']) || strlen($params['stripe_private_key']) >= 5) {
                    $user->setStripePrivateKey(htmlspecialchars(trim($params['stripe_private_key'])));
                    $fields['stripe_private_key'] = true;
                } else {
                    $fields['stripe_private_key'] = false;
                }
            }

            $em->flush();
            return View::render('Franchise/onlinePayment', ['user' => $user, 'page' => 'online_payment', 'fields' => $fields]);
        }

        return View::render('Franchise/onlinePayment', ['user' => $user, 'page' => 'online_payment']);
    }

    public function logoutAction() {
        Session::destroy();
        $this->redirectTo('/panel/login');
    }
}