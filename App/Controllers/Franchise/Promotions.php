<?php

namespace App\Controllers\Franchise;

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
    private $em;
    private $promotionRepository;

    public function __construct()
    {
        $userId = Session::get('user_id');

        $this->em = Entity::getEntityManager();
        $this->promotionRepository = $this->em->getRepository(Promotion::class);

        if (is_null($user = $this->em->find(Users::class, $userId))) {
            $this->redirectTo('/panel/login');
        }

        $this->user = $user;
    }

    public function indexAction() {
        $promotions = $this->promotionRepository->findByUser($this->user);

        return View::render('Franchise/promotions', ['page' => 'promotions', 'user' => $this->user, 'promotions' => $promotions]);
    }

    public function addAction() {
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

            $this->em->persist($promotion);
            $this->em->flush();

            $this->redirectTo('/panel/promotions');
        }

        return View::render('Franchise/addPromotions', ['page' => 'promotion_add', 'user' => $this->user]);

    }
}
