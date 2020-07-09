<?php

namespace App\Controllers\Franchise;

use App\Entity\Orders;
use App\Entity\Users;
use App\Entity\Promotion;
use Core\Controller;
use Core\CSRF;
use Core\Entity;
use Core\Request;
use Core\Session;
use Core\Validator;
use Core\View;

class Promotions extends Controller
{
    private $user;
    private $em;
    private $promotionRepository;

    public function __construct($routes)
    {
        parent::__construct($routes);
        $userId = Session::get('user_id');

        $this->em = Entity::getEntityManager();
        $this->promotionRepository = $this->em->getRepository(Promotion::class);

        if (is_null($user = $this->em->find(Users::class, $userId))) {
            $this->redirectTo('/panel/login');
        }

        $this->user = $user;
    }

    public function indexAction() {

        if(is_null($this->user->getTruck()) || !$this->user->getIsActive()) {
            return View::render('Franchise/noTruck', ['user' => $this->user]);
        }

        $promotions = $this->promotionRepository->findBy(['user'=> $this->user, 'isArchived' => 0]);
        $ordersRepository = $this->em->getRepository(Orders::class);

        foreach ($promotions as $key => $promotion) {
            $promotions[$key]->usage = $ordersRepository->count(['promotion' => $promotion]);
        }

        return View::render('Franchise/promotions', ['page' => 'promotions', 'user' => $this->user, 'promotions' => $promotions]);
    }

    public function archivesAction() {

        if(is_null($this->user->getTruck()) || !$this->user->getIsActive()) {
            return View::render('Franchise/noTruck', ['user' => $this->user]);
        }

        $promotions = $this->promotionRepository->findBy(['user'=> $this->user, 'isArchived' => 1]);
        $ordersRepository = $this->em->getRepository(Orders::class);

        foreach ($promotions as $key => $promotion) {
            $promotions[$key]->usage = $ordersRepository->count(['promotion' => $promotion]);
        }

        return View::render('Franchise/promotionsArchived', ['page' => 'promotions_archives', 'user' => $this->user, 'promotions' => $promotions]);
    }

    public function addAction() {

        if(is_null($this->user->getTruck()) || !$this->user->getIsActive()) {
            return View::render('Franchise/noTruck', ['user' => $this->user]);
        }

        CSRF::generate();

        if(Request::isPost()) {
            CSRF::validate();
            $params = Request::getAllParams();

            if(
                empty($params['name'])
                || empty($params['start_at'])
                || empty($params['end_at'])
                || empty($params['price_min'])
                || empty($params['price_max'])
                || empty($params['reduc_percentage'])
            ) {
                return View::render('Franchise/addPromotions', ['page' => 'promotion_add', 'user' => $this->user, 'error' => true, 'params' => $params]);
            }

            if(!empty($params['max_commands']) && $params['max_commands'] > 0) {
                $maxCommands = intval(trim($params['max_commands']));
            } else {
                $maxCommands = null;
            }

            $startAt = new \DateTime($params['start_at']);
            $endAt = new \DateTime($params['end_at']);

            $priceMin = doubleval(htmlspecialchars(trim($params['price_min'])));
            $priceMax = doubleval(htmlspecialchars(trim($params['price_max'])));

            $reducPercentage = doubleval(htmlspecialchars(trim($params['reduc_percentage'])));

            if((time() > $startAt->getTimestamp() && $endAt->getTimestamp() <= $startAt->getTimestamp()) || ($priceMin > $priceMax) && $reducPercentage > 0) {
                return View::render('Franchise/addPromotions', ['page' => 'promotion_add', 'user' => $this->user, 'error' => true, 'params' => $params]);
            }

            $promotion = new Promotion();
            $promotion->setName(htmlspecialchars(trim($params['name'])));
            $promotion->setStartAt($startAt);
            $promotion->setEndAt($endAt);
            $promotion->setMinPrice($priceMin);
            $promotion->setMaxPrice($priceMax);
            $promotion->setReducPercentage($reducPercentage);
            $promotion->setUser($this->user);
            $promotion->setIsArchived(0);
            $promotion->setMaxCommands($maxCommands);

            $this->em->persist($promotion);
            $this->em->flush();

            $this->redirectTo('/panel/promotions');
        }

        return View::render('Franchise/addPromotions', ['page' => 'promotion_add', 'user' => $this->user]);
    }

    public function editAction(){

        if(is_null($this->user->getTruck()) || !$this->user->getIsActive()) {
            return View::render('Franchise/noTruck', ['user' => $this->user]);
        }

        $promotion = $this->promotionRepository->find($this->getRouteParameter('id'));

        if(is_null($promotion) || $promotion->getUser() != $this->user) {
            $this->redirectTo('/panel/promotions');
        }

        if($promotion->getIsArchived() == 1) {
            $this->redirectTo('/panel/promotions');
        }

        $ordersRepository = $this->em->getRepository(Orders::class);
        $usage = $ordersRepository->count(['promotion' => $promotion]);
        $promotion->usage = $usage;

        CSRF::generate();
        if(Request::isPost()) {
            CSRF::validate();
            $params = Request::getAllParams();
            $fields = [];

            $name = htmlspecialchars(trim($params['name']));
            if($name != $promotion->getName()) {
                if(!empty($name)) {
                    $promotion->setName($name);
                    $fields['name'] = true;
                } else {
                    $fields['name'] = false;
                }
            }

            if(!empty($params['max_commands']) || $params['max_commands'] > 0) {
                $maxCommands = intval(trim($params['max_commands']));
                if($maxCommands > $promotion->getMaxCommands()) {
                    if($maxCommands > $usage) {
                        $promotion->setMaxCommands($maxCommands);
                        $fields['max_commands'] = true;
                    } else {
                        $fields['max_commands'] = false;
                    }
                }
            } else {
                $promotion->setMaxCommands(0);
                $fields['max_commands'] = true;
            }

            $startAt = new \DateTime($params['start_at']);
            $endAt = new \DateTime($params['end_at']);

            if($endAt->getTimestamp() <= $startAt->getTimestamp()) {
                $startAt = $promotion->getStartAt();
                $endAt = $promotion->getEndAt();
                $fields['start_at'] = $fields['end_at'] = false;
            } else {

                if($startAt != $promotion->getStartAt()) {
                    $promotion->setStartAt($startAt);
                    $fields['start_at'] = true;
                }

                if($endAt != $promotion->getEndAt()) {
                    $promotion->setEndAt($endAt);
                    $fields['end_at'] = true;
                }
            }

            $priceMin = doubleval(htmlspecialchars(trim($params['price_min'])));
            $priceMax = doubleval(htmlspecialchars(trim($params['price_max'])));

            if($priceMin >= $priceMax) {
                $priceMin = $promotion->getMinPrice();
                $priceMax = $promotion->getMaxPrice();
                $fields['price_min'] = $fields['price_max'] = false;
            } else {

                if($priceMin != $promotion->getMinPrice()) {
                    $promotion->setMinPrice($priceMin);
                    $fields['price_min'] = true;
                }

                if($priceMax != $promotion->getMaxPrice()) {
                    $promotion->setMaxPrice($priceMax);
                    $fields['price_max'] = true;
                }
            }

            $reducPercentage = doubleval(htmlspecialchars(trim($params['reduc_percentage'])));
            if($reducPercentage != $promotion->getReducPercentage()) {
                if($reducPercentage > 0) {
                    $promotion->setReducPercentage($reducPercentage);
                    $fields['reduc_percentage'] = true;
                } else {
                    $fields['reduc_percentage'] = false;
                }
            }

            $this->em->flush();
            return View::render('Franchise/editPromotions', ['page' => 'promotions', 'user' => $this->user, 'promotion' => $promotion, 'fields' => $fields]);
        }

        return View::render('Franchise/editPromotions', ['page' => 'promotions', 'user' => $this->user, 'promotion' => $promotion]);
    }

    public function archiveAction() {

        if(is_null($this->user->getTruck()) || !$this->user->getIsActive()) {
            return View::render('Franchise/noTruck', ['user' => $this->user]);
        }

        $promotion = $this->promotionRepository->find($this->getRouteParameter('id'));

        if(is_null($promotion) || $promotion->getUser() != $this->user) {
            $this->redirectTo('/panel/promotions');
        }

        if($promotion->getIsArchived() == 0) {
            $promotion->setIsArchived(1);
        } else {
            $promotion->setIsArchived(0);
        }

        $this->em->flush();

        if($promotion->getIsArchived()) {
            $this->redirectTo('/panel/promotions/archives');
        }

        $this->redirectTo('/panel/promotions');
    }
}
