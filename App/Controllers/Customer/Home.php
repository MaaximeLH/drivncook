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

class Home extends Controller {


    public function loginAction() {
        if (Session::get('customer_id') !== false) {
            return $this->redirectTo('/customers/logout');
        }

        CSRF::generate();

        if (Request::isPost()) {
            CSRF::validate();
            $params = Request::getAllParams();

            $em = Entity::getEntityManager();
            $repository = $em->getRepository(Customer::class);

            $user = $repository->findOneByEmail($params['email']);

            if (is_null($user) || !password_verify($params['password'], $user->getPassword())) {
                return View::render('Customers/login', ['loginError' => true]);
            }

            Session::set('customer_id', $user->getId());
            $this->redirectTo('/customers');
        }

        return View::render('Customers/login');
    }

    public function registerAction() {
        if (Session::get('customer_id') !== false) {
            return $this->redirectTo('/customers/logout');
        }

        CSRF::generate();

        $em = Entity::getEntityManager();
        $customersReposisory = $em->getRepository(Customer::class);

        if (Request::isPost()) {
            CSRF::validate();
            $params = Request::getAllParams();
            $errors = [];

            $em = Entity::getEntityManager();
            $repository = $em->getRepository(Customer::class);

            if(empty($params['lastname']) || !Validator::isValidName($params['lastname'])) {
                $errors['lastname'] = true;
            }

            if(empty($params['firstname']) || !Validator::isValidName($params['firstname'])) {
                $errors['firstname'] = true;
            }

            if(!Validator::isValidEmail($params['email']) || !is_null($repository->findOneByEmail($params['email']))) {
                $errors['email'] = true;
            }

            if(!Validator::isGoodPassword($params['password']) || !Validator::isGoodPassword($params['password_confirm']) || $params['password'] != $params['password_confirm']) {
                $errors['password'] = $errors['password_confirm'] = true;
            }

            if(!empty($errors)) {
                return View::render('Customers/register', ['params' => $params, 'errors' => $errors]);
            }

            $customer = new Customer();
            $customer->setEmail(htmlspecialchars(trim($params['email'])));
            $customer->setFirstName(htmlspecialchars(trim($params['firstname'])));
            $customer->setLastName(htmlspecialchars(trim($params['lastname'])));
            $customer->setPassword(password_hash($params['password'], PASSWORD_DEFAULT));

            $em->persist($customer);
            $em->flush();
            Session::set('customer_id', $customer->getId());
            return $this->redirectTo('/customers');
        }

        return View::render('Customers/register');
    }

    public function logoutAction()
    {
        Session::destroy();
        $this->redirectTo('/customers/login');
    }
}