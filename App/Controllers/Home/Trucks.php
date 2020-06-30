<?php

namespace App\Controllers\Home;

use App\Entity\Users;
use App\Entity\Card;
use App\Entity\CardCategory;
use App\Entity\CardItem;
use Core\Controller;
use Core\CSRF;
use Core\View;
use Core\Entity;
use App\Entity\Truck;

class Trucks extends Controller {

    private $em;
    private $truckRepository;

    public function __construct($routes)
    {
        parent::__construct($routes);
        $this->em = Entity::getEntityManager();
        $this->truckRepository = $this->em->getRepository(Truck::class);

    }

    public function indexAction() {
        $trucks = $this->truckRepository->findAll();
        $usersRepository = $this->em->getRepository(Users::class);

        $trucksLocates = [];
        foreach ($trucks as $key => $truck) {
            $trucks[$key]->user = $usersRepository->findOneByTruck($truck);
            if($trucks[$key]->user === null) {
                unset($trucks[$key]);
                continue;
            }

            if (!empty($truck->getLongitude()) && !empty($truck->getLatitude())) {
                $trucksLocates[] = ['lon' => $truck->getLongitude(), 'lat' => $truck->getLatitude()];
            }
        }

        return View::render('Home/trucks', ['page' => 'trucks', 'trucks' => $trucks, 'trucksLocates' => json_encode($trucksLocates)]);
    }

    public function commandsAction() {
        CSRF::generate();

        $truck = $this->truckRepository->find($this->getRouteParameter('id'));
        if(is_null($truck)) {
            return $this->redirectTo('/trucks');
        }

        $usersRepository = $this->em->getRepository(Users::class);
        if(is_null($user = $usersRepository->findOneByTruck($truck))) {
            return $this->redirectTo('/trucks');
        }

        $truck->user = $user;

        return View::render('Home/commands', ['page' => 'trucks', 'truck' => $truck]);

    }
    public function menuAction() 
    {
        CSRF::generate();
        $truck = $this->truckRepository->find($this->getRouteParameter('id'));
        if(is_null($truck)) {
            return $this->redirectTo('/trucks');
        }

        $usersRepository = $this->em->getRepository(Users::class);
        if(is_null($user = $usersRepository->findOneByTruck($truck))) {
            return $this->redirectTo('/trucks');
        }
        $cardRepository = $this->em->getRepository(Card::class);
        $cardCategoryRepository = $this->em->getRepository(CardCategory::class);
        $cardItemRepository = $this->em->getRepository(CardItem::class);
        $cards = $cardRepository->findByUser($user);
        foreach ($cards as $card) {
            $cardCategories[$card->getId()] = $cardCategoryRepository->findByCard($card);
            $cardItems[$card->getId()] = $cardRepository->getCardWithCategoryAndItem($card->getId());
        }

        $data['page'] = 'trucks Menu';
        $data['truck'] = $truck;
        $data['cards'] = $cards;
        $data['cardCategories'] = $cardCategories;
        $data['cardItems'] = $cardItems;
        $data['user'] = $user;
        return View::render('Home/menu', $data);

    }
}