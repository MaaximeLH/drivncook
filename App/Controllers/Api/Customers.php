<?php

namespace App\Controllers\Api;

use App\Entity\Customer;
use App\Entity\FidelityCard;
use Core\Controller;
use Core\Entity;
use Core\Request;

class Customers extends Controller {

    public function loginAction() {

        Request::assertPostOnly();
        $params = Request::getAllParams();
        if(empty($params['email']) || empty($params['password'])) {
            die(json_encode(['error' => 'true']));
        }

        $email = htmlspecialchars(trim($params['email']));
        $password = htmlspecialchars(trim($params['password']));

        $em = Entity::getEntityManager();
        $customersRepository = $em->getRepository(Customer::class);

        $customer = $customersRepository->findOneByEmail($email);
        if(is_null($customer) || !password_verify($password, $customer->getPassword())) {
            die(json_encode(['error' => 'true']));
        }

        $response = [
            'lastname' => $customer->getLastname(),
            'firstname' => $customer->getFirstname(),
            'email' => $customer->getEmail(),
            'id' => $customer->getId(),
            'point' => '0',
            'error' => 'false'
        ];

        $fidelityCardRepository = $em->getRepository(FidelityCard::class);
        $fidelity = $fidelityCardRepository->findOneByCustomer($customer);
        if(is_null($fidelity)){
            die(json_encode($response));
        }

        $response['point'] = intval(trim($fidelity->getNbPoint()));
        die(json_encode($response));
    }

}