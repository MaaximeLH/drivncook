<?php

namespace App\Controllers\Home;

use App\Entity\Customer;
use App\Entity\FidelityCard;
use App\Entity\Invoice;
use App\Entity\InvoiceLine;
use App\Entity\Openings;
use App\Entity\OrderLine;
use App\Entity\Orders;
use App\Entity\Promotion;
use App\Entity\Users;
use App\Entity\Card;
use App\Entity\CardCategory;
use App\Entity\CardItem;
use Core\Controller;
use Core\CSRF;
use Core\Request;
use Core\Session;
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
        $openingsRepository = $this->em->getRepository(Openings::class);
        $cardRepository = $this->em->getRepository(Card::class);

        $trucksLocates = [];
        foreach ($trucks as $key => $truck) {
            $user = $usersRepository->findOneByTruck($truck);
            if(is_null($user) || !$user->getIsActive() || is_null($cardRepository->findOneByUser($user))) {
                unset($trucks[$key]);
                continue;
            }

            $trucks[$key]->user = $user;

            $openings = $openingsRepository->findOneByUser($user);
            if(is_null($openings)) {
                $this->_createOpeningsUsers($user);
                $openings = $openingsRepository->findOneByUser($user);
            }

            if($openings->getGlobalOpen() == 0) {
                unset($trucks[$key]);
                continue;
            }

            $trucks[$key]->openings = $openings;

            $trucks[$key]->isOpen = $this->_isOpen($openings);

            if (!empty($truck->getLongitude()) && !empty($truck->getLatitude())) {
                $trucksLocates[] = ['lon' => $truck->getLongitude(), 'lat' => $truck->getLatitude()];
            }
        }

        return View::render('Home/trucks', ['page' => 'trucks', 'trucks' => $trucks, 'trucksLocates' => json_encode($trucksLocates), 'functions' => $this->_guettersForDays()]);
    }

    public function commandsAction() {
        CSRF::generate();

        $truck = $this->truckRepository->find($this->getRouteParameter('id'));
        if(is_null($truck)) {
            return $this->redirectTo('/trucks');
        }

        $cardRepository = $this->em->getRepository(Card::class);
        $card = $cardRepository->find($this->getRouteParameter('card'));
        if(is_null($card)) {
            return $this->redirectTo('/trucks/menu/' . $truck->getId());
        }

        $usersRepository = $this->em->getRepository(Users::class);
        if(is_null($user = $usersRepository->findOneByTruck($truck))) {
            return $this->redirectTo('/trucks');
        }

        $truck->user = $user;

        if(!$user->getIsActive()) {
            return $this->redirectTo('/trucks');
        }

        $openingsRepository = $this->em->getRepository(Openings::class);
        $openings = $openingsRepository->findOneByUser($user);
        $isOpen = $this->_isOpen($openings);

        $customer = Session::get('customer_id');
        $customerRepository = $this->em->getRepository(Customer::class);
        $customer = $customerRepository->find($customer);
        if($customer == false){
            return $this->redirectTo('/trucks');
        }

        $items = $cardRepository->getCardWithCategoryAndItem($card->getId());
        $total = 0;
        foreach ($items as $item) {
            $total += $item['card_item_price'];
        }

        $promotionRepository = $this->em->getRepository(Promotion::class);
        $ordersRepository = $this->em->getRepository(Orders::class);
        $allPromotions = $promotionRepository->findBy(['user' => $user, 'isArchived' => 0]);

        $promotions = [];
        $promotionsCheck = [];


        foreach ($allPromotions as $promotion) {

            $usage = $ordersRepository->count(['promotion' => $promotion]);

            if(!is_null($promotion->getMaxCommands()) && $promotion->getMaxCommands() > 0 && $usage >= $promotion->getMaxCommands()) {
                continue;
            }

            if(
                $total >= $promotion->getMinPrice()
                && $total <= $promotion->getMaxPrice()
                && time() >= $promotion->getStartAt()->getTimestamp()
                && time() <= $promotion->getEndAt()->getTimestamp()
            ) {
                $promotions[] = $promotion;
                $promotionsCheck[] = $promotion->getId();
            }
        }

        $fidelityCardRepository = $this->em->getRepository(FidelityCard::class);
        $fidelity = $fidelityCardRepository->findOneByCustomer($customer);
        if(is_null($fidelity)) {
            $fidelity = new FidelityCard();
            $fidelity->setNbPoint(0);
            $fidelity->setCustomer($customer);

            $this->em->persist($fidelity);
            $this->em->flush();

            $fidelity = $fidelityCardRepository->findOneByCustomer($customer);
        }

        $fidelityPromotions = [];
        /**
         * Fidélité maximale
         * 50 points  = 10%
         * 100 points = 20%
         * 150 points = 30%
         * 200 points = 40%
         * 250 points = 50%
         */
        $points = 50; $reduc = 10;
        for($i = 0; $i <= 4; $i++) {
            if(($points) <= $fidelity->getNbPoint()) {
                $fidelityPromotions[$i] = ['point' => $points, 'reduc' => $reduc];
            }

            $points += 50;
            $reduc += 10;
        }

        if(Request::isPost()) {
            CSRF::validate();
            $params = Request::getAllParams();

            $datetime = $params['datetime'];
            $datetime = new \DateTime($datetime);
            $recuperation_date = $datetime;
            $datetime = $datetime->getTimestamp();

            $date = new \DateTime();
            $diff = $date->diff(new \DateTime(date('Y-m-d H:i:s', $datetime)));

            $isFidelityPromotion = false;
            if(isset($params['promotions'])) {

                $fidelityPatterns = explode('|', $params['promotions']);
                if($fidelityPatterns[0] != "fidelity" && $params['promotions'] > 0) {
                    $promotion = intval($params['promotions']);
                    $promotion = $promotionRepository->find($promotion);
                } else {
                    if(isset($fidelityPatterns[1]) && in_array($fidelityPatterns[1], [0,1,2,3,4])) {
                        $isFidelityPromotion = $fidelityPromotions[$fidelityPatterns[1]];
                    } else {
                        $promotion = null;
                    }
                }
            } else {
                $promotion = null;
            }

            if($datetime <= time() || $diff->days > 5 || !$this->_isOpeningDays($openings, $recuperation_date)) {
                return View::render('Home/commands', ['page' => 'trucks', 'fidelityPromotions' => $fidelityPromotions, 'truck' => $truck, 'customer' => $customer, 'card' => $card, 'items' => $items, 'datetime_error' => true, 'params' => $params, 'promotions' => $promotions, 'isOpen' => $isOpen, 'functions' => $this->_guettersForDays(), 'user' => $user, 'openings' => $openings]);
            }

            if(isset($params['promotions']) && $params['promotions'] > 0 && !$isFidelityPromotion) {
                if(is_null($params['promotions']) || !in_array($params['promotions'], $promotionsCheck)) {
                    return View::render('Home/commands', ['page' => 'trucks', 'fidelityPromotions' => $fidelityPromotions, 'truck' => $truck, 'customer' => $customer, 'card' => $card, 'items' => $items, 'promotion_error' => true, 'params' => $params, 'promotions' => $promotions, 'isOpen' => $isOpen, 'functions' => $this->_guettersForDays(), 'user' => $user, 'openings' => $openings]);
                }
            }

            $toRemoveFidelity = false;
            if(is_null($promotion) && !$isFidelityPromotion) {
                $delta = 1;
            } else if(!is_null($promotion) && !$isFidelityPromotion) {
                $delta = 1 - ($promotion->getReducPercentage() / 100);
            } else {
                $delta = 1 - ($isFidelityPromotion['reduc'] / 100);
                $toRemoveFidelity = $isFidelityPromotion['point'];
            }

            $order = new Orders();
            $order->setCustomer($customer);
            $order->setUser($user);
            $order->setStatus(1);
            $order->setDescription(htmlspecialchars(trim($params['description'])));
            $order->setRecuperationDate($datetime);

            if(is_null($promotion)) {
                $order->setPromotion(null);
            } else {
                $order->setPromotion($promotion);
            }

            $this->em->persist($order);
            $this->em->flush();

            $total = 0;
            foreach ($items as $item) {
                $orderLine = new OrderLine();
                $orderLine->setOrder($order);
                $orderLine->setText($item['card_item_name']);
                $orderLine->setQuantity(1);
                $orderLine->setPrice($item['card_item_price'] * $delta);

                $this->em->persist($orderLine);
                $this->em->flush();

                $total += $item['card_item_price'];
            }

            if(is_null($promotion) && !$isFidelityPromotion) {
                $fidelity->setNbPoint($fidelity->getNbPoint() + $total);
                $this->em->flush();
            }

            if(is_array($isFidelityPromotion)) {
                $fidelity->setNbPoint($fidelity->getNbPoint() - $toRemoveFidelity);
                $this->em->flush();
            }

            $invoice = new Invoice();
            $invoice->setUser($user);
            $invoice->setCustomer($customer);
            $invoice->setRecipient($customer->getLastName() . " " . $customer->getFirstName());
            $invoice->setOwner($user->getSocietyName());
            $invoice->setOwnerAddressLine($user->getAddressLine());
            $invoice->setOwnerCity($user->getAddressCity());
            $invoice->setOwnerPostalCode($user->getPostalCode());
            $invoice->setStatus(false);

            $this->em->persist($invoice);
            $this->em->flush();

            foreach ($items as $item) {
                $invoiceLine = new InvoiceLine();
                $invoiceLine->setInvoice($invoice);
                $invoiceLine->setText($item['card_item_name']);
                $invoiceLine->setQuantity(1);
                $invoiceLine->setPrice($item['card_item_price'] * $delta);
                $invoiceLine->setTva(20.00);

                $this->em->persist($invoiceLine);
                $this->em->flush();
            }

            return $this->redirectTo("/customers/commands");
        }

        return View::render('Home/commands', ['page' => 'trucks', 'truck' => $truck, 'customer' => $customer, 'fidelityPromotions' => $fidelityPromotions, 'user' => $user, 'card' => $card, 'items' => $items, 'promotions' => $promotions, 'openings' => $openings, 'isOpen' => $isOpen, 'functions' => $this->_guettersForDays()]);

    }
    public function menuAction()
    {
        CSRF::generate();
        $truck = $this->truckRepository->find($this->getRouteParameter('id'));
        if(is_null($truck)) {
            return $this->redirectTo('/trucks');
        }

        $customer = Session::get('customer_id');
        $customerRepository = $this->em->getRepository(Customer::class);
        $customer = $customerRepository->find($customer);

        $usersRepository = $this->em->getRepository(Users::class);
        if(is_null($user = $usersRepository->findOneByTruck($truck))) {
            return $this->redirectTo('/trucks');
        }

        if(!$user->getIsActive()) {
            return $this->redirectTo('/trucks');
        }

        $openingsRepository = $this->em->getRepository(Openings::class);
        $openings = $openingsRepository->findOneByUser($user);

        $cardRepository = $this->em->getRepository(Card::class);
        $cardCategoryRepository = $this->em->getRepository(CardCategory::class);
        $cards = $cardRepository->findByUser($user);
        $cardCategories = $cardItems = [];
        foreach ($cards as $card) {
            $cardCategories[$card->getId()] = $cardCategoryRepository->findByCard($card);
            $cardItems[$card->getId()] = $cardRepository->getCardWithCategoryAndItem($card->getId());
        }

        $data['page'] = 'trucks';
        $data['truck'] = $truck;
        $data['cards'] = $cards;
        $data['cardCategories'] = $cardCategories;
        $data['cardItems'] = $cardItems;
        $data['user'] = $user;
        $data['customer'] = $customer;
        $data['openings'] = $openings;
        $data['isOpen'] = $this->_isOpen($openings);
        $data['functions'] = $this->_guettersForDays();
        return View::render('Home/menu', $data);

    }

    public function findAction() {
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

        $customer = Session::get('customer_id');
        $customerRepository = $this->em->getRepository(Customer::class);
        $customer = $customerRepository->find($customer);
        if($customer == false){
            return $this->redirectTo('/trucks');
        }

        $cardRepository = $this->em->getRepository(Card::class);
        $cardItemRepository = $this->em->getRepository(CardItem::class);
        $items = $cardRepository->getAllItemsOfUser($user->getId());

        $openingsRepository = $this->em->getRepository(Openings::class);
        $openings = $openingsRepository->findOneByUser($user);

        $isOpen = $this->_isOpen($openings);

        if(Request::isPost()) {
            CSRF::validate();
            $params = Request::getAllParams();

            $datetime = new \DateTime($params['datetime']);
            $recuperation_date = $datetime;
            $datetime = $datetime->getTimestamp();

            $date = new \DateTime();
            $diff = $date->diff(new \DateTime(date('Y-m-d H:i:s', $datetime)));

            if($datetime <= time() || $diff->days > 5  || !$this->_isOpeningDays($openings, $recuperation_date)) {
                return View::render('Home/find', ['page' => 'trucks', 'truck' => $truck, 'user' => $user, 'customer' => $customer, 'items' => $items, 'datetime_error' => true, 'params' => $params, 'functions' => $this->_guettersForDays(), 'isOpen' => $isOpen, 'openings' => $openings]);
            }

            $items = [];
            foreach ($params['items'] as $item) {
                $items[] = $cardItemRepository->find($item);
            }

            $order = new Orders();
            $order->setCustomer($customer);
            $order->setUser($user);
            $order->setStatus(1);
            $order->setDescription(htmlspecialchars(trim($params['description'])));
            $order->setRecuperationDate($datetime);

            $this->em->persist($order);
            $this->em->flush();

            foreach ($items as $item) {
                $orderLine = new OrderLine();
                $orderLine->setOrder($order);
                $orderLine->setText($item->getName());
                $orderLine->setQuantity(1);
                $orderLine->setPrice($item->getPrice());

                $this->em->persist($orderLine);
                $this->em->flush();
            }

            $invoice = new Invoice();
            $invoice->setUser($user);
            $invoice->setCustomer($customer);
            $invoice->setRecipient($customer->getLastName() . " " . $customer->getFirstName());
            $invoice->setOwner($user->getSocietyName());
            $invoice->setOwnerAddressLine($user->getAddressLine());
            $invoice->setOwnerCity($user->getAddressCity());
            $invoice->setOwnerPostalCode($user->getPostalCode());
            $invoice->setStatus(false);

            $this->em->persist($invoice);
            $this->em->flush();

            foreach ($items as $item) {
                $invoiceLine = new InvoiceLine();
                $invoiceLine->setInvoice($invoice);
                $invoiceLine->setText($item->getName());
                $invoiceLine->setQuantity(1);
                $invoiceLine->setPrice($item->getPrice());
                $invoiceLine->setTva(20.00);

                $this->em->persist($invoiceLine);
                $this->em->flush();
            }

            return $this->redirectTo("/customers/commands");
        }

        return View::render('Home/find', ['page' => 'trucks', 'truck' => $truck, 'user' => $user, 'customer' => $customer, 'items' => $items, 'functions' => $this->_guettersForDays(), 'isOpen' => $isOpen, 'openings' => $openings]);
    }

    private function _createOpeningsUsers($user) {
        $date = new \DateTime();
        $open = $date->setTime(0, 0);
        $date = new \DateTime();
        $close = $date->setTime(23, 59);

        $opening = new Openings();
        $opening->setUser($user);

        $opening->setMondayOpen($open);
        $opening->setTuesdayOpen($open);
        $opening->setWedOpen($open);
        $opening->setThursdayOpen($open);
        $opening->setFridayOpen($open);
        $opening->setSatOpen($open);
        $opening->setSunOpen($open);

        $opening->setMondayClose($close);
        $opening->setTuesdayClose($close);
        $opening->setWedClose($close);
        $opening->setThursdayClose($close);
        $opening->setFridayClose($close);
        $opening->setSatClose($close);
        $opening->setSunClose($close);

        $opening->setGlobalOpen(1);

        $this->em->persist($opening);
        $this->em->flush();
    }

    private function _isOpen($openings) {

        $days = $this->_guettersForDays();

        $open = call_user_func([$openings, $days[date('N')]['open']]);
        $close = call_user_func([$openings, $days[date('N')]['close']]);

        $now = new \DateTime();
        $now->setDate(1970, 01, 01);

        if($now > $open && $now < $close) {
            return true;
        }

        return false;
    }

    private function _guettersForDays() {
        return [
            1 => [
                'open' => 'getMondayOpen',
                'close' => 'getMondayClose'
            ],
            2 => [
                'open' => 'getTuesdayOpen',
                'close' => 'getTuesdayClose'
            ],
            3 => [
                'open' => 'getWedOpen',
                'close' => 'getWedClose'
            ],
            4 => [
                'open' => 'getThursdayOpen',
                'close' => 'getThursdayClose'
            ],
            5 => [
                'open' => 'getFridayOpen',
                'close' => 'getFridayClose'
            ],
            6 => [
                'open' => 'getSatOpen',
                'close' => 'getSatClose'
            ],
            7 => [
                'open' => 'getSunOpen',
                'close' => 'getSunClose'
            ],
        ];
    }

    private function _isOpeningDays($openings, $datetime) {
        $days = $this->_guettersForDays();
        $opening = $days[$datetime->format('N')];
        $open = call_user_func([$openings, $opening['open']]);
        $close = call_user_func([$openings, $opening['close']]);

        $compare = new \DateTime();
        $compare->setDate(1970, 01, 01);
        $compare->setTime($datetime->format('H'), $datetime->format('i'), $datetime->format('s'));

        if($compare > $open && $compare < $close) {
            return true;
        }

        return false;
    }
}