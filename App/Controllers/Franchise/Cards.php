<?php

namespace App\Controllers\Franchise;

use App\Entity\Card as CardEntity;
use App\Entity\Users;
use App\Entity\CardCategory;
use App\Entity\CardItem;
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
    private $em;
    private $cardsRepository;
    private $cardCategoryRepository;
    private $cardItemRepository;

    public function __construct($routes)
    {
        parent::__construct($routes);

        $this->em = Entity::getEntityManager();


        $userId = Session::get('user_id');
        if (is_null($user = $this->em->find(Users::class, $userId))) {
            return $this->redirectTo('/panel/login');
        }

        $this->cardsRepository = $this->em->getRepository(CardEntity::class);
        $this->cardCategoryRepository = $this->em->getRepository(CardCategory::class);
        $this->cardItemRepository = $this->em->getRepository(CardItem::class);
        $this->user = $user;
    }

    public function indexAction()
    {

        if(is_null($this->user->getTruck())) {
            return View::render('Franchise/noTruck', ['user' => $this->user]);
        }

        $cards = $this->cardsRepository->findBy(['user' => $this->user]);
        CSRF::generate();

        return View::render('Franchise/card_listing', ['user' => $this->user, 'page' => 'card', 'cards' => $cards]);
    }

    public function addAction()
    {
        if(is_null($this->user->getTruck())) {
            return View::render('Franchise/noTruck', ['user' => $this->user]);
        }

        $userRepository = $this->em->getRepository(Users::class);
        $user = $userRepository->find($this->user->getId());
        CSRF::generate();

        if (Request::isPost()) {
            CSRF::validate();

            $params = Request::getAllParams();
            $card = new CardEntity();
            if (Validator::isValidName($params['name'])) {
                $card->setName(trim($params['name']));
            }
            $card->setUser($user);

            $this->em->persist($card);
            $this->em->flush();

            echo '<div class="alert alert-success" role="alert">le menu a bien été crée</div>';
        }
    }

    public function editAction()
    {

        if(is_null($this->user->getTruck())) {
            return View::render('Franchise/noTruck', ['user' => $this->user]);
        }

        $cardItems = $this->cardsRepository->getCardWithCategoryAndItem($this->getRouteParameter('id'));
        $card = $this->cardsRepository->find($this->getRouteParameter('id'));


        $cardCategories = $this->cardCategoryRepository->findBy(['card' => $card->getId()]);

        CSRF::generate();
        if (Request::isPost()) {
            CSRF::validate();
            $params = Request::getAllParams();

            if (Validator::isValidName($params['name'])) {
                $card->setName(trim($params['name']));
            }
            $this->em->flush();

            $this->saveCardCategoriesAndItem($params, $card);
            $this->redirectTo($this->currentUrl());
        }

        return View::render('Franchise/edit_card', ['user' => $this->user, 'Page' => 'Edit card', 'card' => $card, 'cardItems' => $cardItems, 'cardCategories' => $cardCategories]);
    }

    public function deleteAction()
    {
        if (!Request::isDelete()) {
            return false;
        }

        CSRF::validate();
        if (is_null($card = $this->cardsRepository->find($this->getRouteParameter('id')))) {
            return $this->redirectTo("/panel/card");
        }
        $this->em->remove($card);
        $this->em->flush();
        $this->redirectTo('/panel/card');
    }

    public function deleteCategoryAction()
    {

        if(is_null($this->user->getTruck())) {
            return View::render('Franchise/noTruck', ['user' => $this->user]);
        }

        if (is_null($cardCategory = $this->cardCategoryRepository->find($this->getRouteParameter('id')))) {
            return $this->redirectTo("/panel/card");
        }
        $cardItems = $this->cardItemRepository->findBy(['category' => $cardCategory]);

        foreach ($cardItems as $cardItem) {
            $this->em->remove($cardItem);
            $this->em->flush();
        }

        $this->em->remove($cardCategory);
        $this->em->flush();
        $this->redirectTo('/panel/card/edit/' . $cardCategory->getCard()->getId());
    }

    public function deleteItemAction()
    {

        if(is_null($this->user->getTruck())) {
            return View::render('Franchise/noTruck', ['user' => $this->user]);
        }

        if (is_null($cardItem = $this->cardItemRepository->find($this->getRouteParameter('id')))) {
            return $this->redirectTo("/panel/card");
        }
        $this->em->remove($cardItem);
        $this->em->flush();
        $this->redirectTo('/panel/card/edit/' . $cardItem->getCategory()->getCard()->getId());
    }

    private function saveCardCategoriesAndItem(&$params, $card)
    {
        if (isset($params['cardCategories'])) {
            for ($i = 0; $i < count($params['cardCategories']); $i += 1) {
                $cardCategoriesId = $params['cardCategoriesId'][$i];
                $cardCategoriesName = str_replace(' ', '_', $params['cardCategories'][$i]);
                $category = $this->cardCategoryRepository->find(
                    !empty($cardCategoriesId) ?
                        $cardCategoriesId :
                        0
                );
                if ($category === null) {
                    $category = new CardCategory();
                }
                $category->setName(trim($cardCategoriesName));
                $category->setCard($card);
                $this->em->persist($category);
                $this->em->flush();
                if (isset($params[$cardCategoriesName])) {
                    for ($j = 0; $j < count($params[$cardCategoriesName]); $j += 1) {
                        $cardItem = $this->cardItemRepository->find(
                            !empty($params[$cardCategoriesName . 'Id'][$j]) ?
                                $params[$cardCategoriesName . 'Id'][$j] :
                                0
                        );
                        if ($cardItem === null) {
                            $cardItem = new CardItem();
                        }
                        $cardItem->setName(trim($params[$cardCategoriesName][$j]));
                        $cardItem->setPrice(
                            !empty($params[$cardCategoriesName . 'Price'][$j]) ?
                                trim($params[$cardCategoriesName . 'Price'][$j]) :
                                NULL
                        );
                        $cardItem->setCategory($category);
                        $this->em->persist($cardItem);
                        $this->em->flush();
                    }
                }
            }
        }
    }
}
