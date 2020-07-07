<?php

namespace App\Controllers\Api;

use App\Entity\Customer;
use App\Entity\FidelityCard;
use Core\Controller;
use Core\Entity;
use Core\Request;

class Customers extends Controller {

    public function loginAction() {

        $params = Request::getAllParams();
        if(empty($params['email']) || empty($params['password'])) {
            die('false');
        }

        $email = htmlspecialchars(trim($params['email']));
        $password = htmlspecialchars(trim($params['password']));

        $em = Entity::getEntityManager();
        $customersRepository = $em->getRepository(Customer::class);

        $customer = $customersRepository->findOneByEmail($email);
        if(is_null($customer) || !password_verify($password, $customer->getPassword())) {
            die('false');
        }

        $fidelityCardRepository = $em->getRepository(FidelityCard::class);
        $fidelity = $fidelityCardRepository->findOneByCustomer($customer);
        if(is_null($fidelity)){
            die('0');
        }

        echo intval(trim($fidelity->getNbPoint()));
        die();
    }

}