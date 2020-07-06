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

        $this->layout = "Franchise/assets/layout.phtml";
        $this->em = Entity::getEntityManager();
        $this->promotionRepository = $this->em->getRepository(Promotion::class);

        if (is_null($user = $this->em->find(Users::class, $userId))) {
            return $this->redirectTo('/panel/login');
        }
        $this->user = $user;
    }

    public function indexAction()
    {
        $data['user'] = $this->user;
        $data['promotions'] = $this->promotionRepository->findAll();
        $data['breadcrumb'] = ['Promotion', 'liste'];
        $this->load_view('Franchise/promotion_listing', $data);
    }

    public function loginAction()
    {
        if (Session::get('user_id') !== false) {
            return $this->redirectTo('/panel/logout');
        }

        CSRF::generate();

        if (Request::isPost()) {
            CSRF::validate();
            $params = Request::getAllParams();

            $em = Entity::getEntityManager();
            $repository = $em->getRepository(Users::class);

            $user = $repository->findOneByEmail($params['email']);

            if (is_null($user) || !password_verify($params['password'], $user->getPassword())) {
                return View::render('Franchise/login', ['loginError' => true]);
            }

            Session::set('user_id', $user->getId());
            $this->redirectTo('/panel');
        }

        return View::render('Franchise/login');
    }

    public function logoutAction()
    {
        Session::destroy();
        $this->redirectTo('/panel/login');
    }
}
