<?php

namespace App\Controllers\Home;

use App\Entity\Customer;
use App\Entity\Invoice;
use App\Entity\InvoiceLine;
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

        if(Request::isPost()) {
            CSRF::validate();
            $params = Request::getAllParams();

            $datetime = $params['datetime'];
            $datetime = new \DateTime($datetime);
            $datetime = $datetime->getTimestamp();

            $date = new \DateTime();
            $diff = $date->diff(new \DateTime(date('Y-m-d H:i:s', $datetime)));

            if(isset($params['promotions']) && $params['promotions'] > 0) {
                $promotion = intval($params['promotions']);
                $promotion = $promotionRepository->find($promotion);
            } else {
                $promotion = null;
            }

            if($datetime <= time() || $diff->days > 5) {
                return View::render('Home/commands', ['page' => 'trucks', 'truck' => $truck, 'customer' => $customer, 'card' => $card, 'items' => $items, 'datetime_error' => true, 'params' => $params, 'promotions' => $promotions]);
            }

            if(isset($params['promotions']) && $params['promotions'] > 0) {
                if(is_null($params['promotions']) || !in_array($params['promotions'], $promotionsCheck)) {
                    return View::render('Home/commands', ['page' => 'trucks', 'truck' => $truck, 'customer' => $customer, 'card' => $card, 'items' => $items, 'promotion_error' => true, 'params' => $params, 'promotions' => $promotions]);
                }
            }

            if(is_null($promotion)) {
                $delta = 1;
            } else {
                $delta = 1 - ($promotion->getReducPercentage() / 100);
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

            foreach ($items as $item) {
                $orderLine = new OrderLine();
                $orderLine->setOrder($order);
                $orderLine->setText($item['card_item_name']);
                $orderLine->setQuantity(1);
                $orderLine->setPrice($item['card_item_price'] * $delta);

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

            $invoiceLine = new InvoiceLine();
            $invoiceLine->setInvoice($invoice);
            $invoiceLine->setText($item['card_item_name']);
            $invoiceLine->setQuantity(1);
            $invoiceLine->setPrice($item['card_item_price'] * $delta);
            $invoiceLine->setTva(20.00);

            $this->em->persist($invoiceLine);
            $this->em->flush();

            return $this->redirectTo("/customers/commands");
        }

        return View::render('Home/commands', ['page' => 'trucks', 'truck' => $truck, 'customer' => $customer, 'card' => $card, 'items' => $items, 'promotions' => $promotions]);

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

        if(Request::isPost()) {
            CSRF::validate();
            $params = Request::getAllParams();

            $datetime = new \DateTime($params['datetime']);
            $datetime = $datetime->getTimestamp();

            $date = new \DateTime();
            $diff = $date->diff(new \DateTime(date('Y-m-d H:i:s', $datetime)));

            if($datetime <= time() || $diff->days > 5) {
                return View::render('Home/find', ['page' => 'trucks', 'truck' => $truck, 'user' => $user, 'customer' => $customer, 'items' => $items, 'datetime_error' => true, 'params' => $params]);
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

            $invoiceLine = new InvoiceLine();
            $invoiceLine->setInvoice($invoice);
            $invoiceLine->setText($item->getName());
            $invoiceLine->setQuantity(1);
            $invoiceLine->setPrice($item->getPrice());
            $invoiceLine->setTva(20.00);

            $this->em->persist($invoiceLine);
            $this->em->flush();

            return $this->redirectTo("/customers/commands");
        }

        return View::render('Home/find', ['page' => 'trucks', 'truck' => $truck, 'user' => $user, 'customer' => $customer, 'items' => $items]);
    }
}