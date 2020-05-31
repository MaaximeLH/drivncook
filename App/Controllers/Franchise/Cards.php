<?php

namespace App\Controllers\Franchise;

use App\Entity\Card as CardEntity;
use App\Entity\Users;
use Core\Controller;
use Core\CSRF;
use Core\Entity;
use Core\Request;
use Core\Session;
use Core\Validator;
use Core\View;

class Cards extends Controller
{
    private $user;

    public function __construct($routes)
    {
        parent::__construct($routes);

        $userId = Session::get('user_id');
        $em = Entity::getEntityManager();
        if (is_null($user = $em->find(Users::class, $userId))) {
            return $this->redirectTo('/panel/login');
        }

        $this->user = $user;
    }

    public function indexAction()
    {
        $em = Entity::getEntityManager();
        $cardsRepository = $em->getRepository(CardEntity::class);
        $cards = $cardsRepository->findAll();
        CSRF::generate();


        return View::render('Franchise/card_listing', ['user' => $this->user, 'page' => 'card', 'cards' => $cards]);
    }

    public function addAction()
    {
        $em = Entity::getEntityManager();
        $userRepository = $em->getRepository(Users::class);
        $user = $userRepository->find($this->user->getId());
        if (Request::isPost()) {
            CSRF::validate();

            $params = Request::getAllParams();
            $card = new CardEntity();
            if (Validator::isValidName($params['name'])) {
                $card->setName(trim($params['name']));
            }
            $card->setUser($user);

            $em->persist($card);
            $em->flush();

            echo '<div class="alert alert-success" role="alert">le menu a bien été crée</div>';
        }
    }
}
