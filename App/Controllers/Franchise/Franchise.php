<?php

namespace App\Controllers\Franchise;

use App\Entity\Users;
use Core\Controller;
use Core\CSRF;
use Core\Entity;
use Core\Request;
use Core\Session;
use Core\Validator;
use Core\View;

/**
 * class Home, cette classe est utilisée pour la page d'accueil et le login / logout / mon compte & paiements en ligne
 * @package App\Controllers\Franchise
 */
class Franchise extends Controller
{
    private $user;

    public function __construct($routes)
    {
        parent::__construct($routes);

        $userId = Session::get('user_id');
        $em = Entity::getEntityManager();
        if(is_null($user = $em->find(Users::class, $userId))) {
            return $this->redirectTo('/panel/login');
        }

        $this->user = $user;
    }

    public function myAccountAction() {
        $em = Entity::getEntityManager();

        $usersRepository = $em->getRepository(Users::class);
        // /!\ On récupère une nouvelle instance d'user pour pouvoir la modifier
        // /!\ On ne peut modifier une entité sans l'entity manager qui l'a chargée
        $user = $usersRepository->find($this->user->getId());

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
        $em = Entity::getEntityManager();
        $usersRepository = $em->getRepository(Users::class);

        // /!\ On récupère une nouvelle instance d'user pour pouvoir la modifier
        // /!\ On ne peut modifier une entité sans l'entity manager qui l'a chargée
        $user = $usersRepository->find($this->user->getId());

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

}