<?php

namespace App\Controllers\Customer;

use App\Entity\Customer;
use Core\Controller;
use Core\CSRF;
use Core\Entity;
use Core\Request;
use Core\Session;
use Core\Validator;
use Core\View;

class Customers extends Controller {

    private $em;
    private $customer;

    public function __construct($routes)
    {
        parent::__construct($routes);
        $customerId = Session::get('customer_id');
        $em = Entity::getEntityManager();
        $this->em = $em;

        $customerRepository = $em->getRepository(Customer::class);
        $customer = $customerRepository->find($customerId);

        if(is_null($customer)) {
            return $this->redirectTo('/customers/login');
        }

        $this->customer = $customer;
    }

    public function accountAction() {
        $em = Entity::getEntityManager();

        $usersRepository = $em->getRepository(Customer::class);
        // /!\ On récupère une nouvelle instance d'user pour pouvoir la modifier
        // /!\ On ne peut modifier une entité sans l'entity manager qui l'a chargée
        $user = $usersRepository->find($this->customer->getId());

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
            return View::render('Customers/account', ['customer' => $user, 'page' => 'account', 'fields' => $fields]);
        }

        return View::render('Customers/account', ['customer' => $this->customer, 'page' => 'account']);
    }

}