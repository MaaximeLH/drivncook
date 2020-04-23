<?php

namespace App\Controllers\Admin;

use App\Entity\Admin;
use App\Entity\Warehouse;
use App\Entity\WarehouseCategory;
use App\Entity\WarehouseItem;
use App\Entity\WarehouseStockItem;
use Core\Controller;
use Core\CSRF;
use Core\Entity;
use Core\Request;
use Core\Validator;
use Core\View;

class Stocks extends Controller {

    private function getUser($id) {
        $em = Entity::getEntityManager();
        $repository = $em->getRepository(Admin::class);
        $user = $repository->find($id);
        return $user;
    }

    public function stocksAction() {
        $userId = $this->logged();
        if(!$userId) {
            return $this->redirectTo('/administration/login');
        }

        $user = $this->getUser($userId);

        $em = Entity::getEntityManager();

        // Repository
        $warehousesRepository = $em->getRepository(Warehouse::class);
        $warehouseStockItemRepository = $em->getRepository(WarehouseStockItem::class);
        $warehouseCategoryRepository = $em->getRepository(WarehouseCategory::class);
        $categories = $warehouseCategoryRepository->findAll();
        $categories = $this->retrieveParentsCategories($categories);

        $warehouseId = $this->getRouteParameter('id');
        $warehouse = $warehousesRepository->findOneById($warehouseId);

        $stocks = $warehouseStockItemRepository->findByWarehouse($warehouse);
        foreach ($stocks as $key => $stock) {
            $products = $warehouseStockItemRepository->findBy(['item' => $stock, 'warehouse' => $warehouse]);
            $stocks[$key]->total = 0;
            foreach ($products as $item) {
                $stocks[$key]->total += $item->getQuantity();
            }
        }

        if(is_null($warehouse)) {
            return $this->redirectTo('/administrations/warehouses');
        }

        return View::render('Admin/stocks', ['user' => $user, 'warehouse' => $warehouse, 'page' => 'warehouses', 'stocks' => $stocks, 'categories' => $categories]);
    }

    public function stocksCategoriesAction() {
        $userId = $this->logged();
        if(!$userId) {
            return $this->redirectTo('/administration/login');
        }

        $user = $this->getUser($userId);
        CSRF::generate();

        $em = Entity::getEntityManager();
        $warehouseCategory = $em->getRepository(WarehouseCategory::class);

        $categories = $warehouseCategory->findAll();
        $categories = $this->retrieveParentsCategories($categories);

        return View::render('Admin/stockCategories', ['user' => $user, 'page' => 'stocks_category', 'categories' => $categories]);
    }

    public function addCategoryAction() {
        $userId = $this->logged();
        if(!$userId) {
            return $this->redirectTo('/administration/login');
        }

        $user = $this->getUser($userId);

        $em = Entity::getEntityManager();
        $warehouseCategoryRepository = $em->getRepository(WarehouseCategory::class);
        CSRF::generate();

        $parents = $warehouseCategoryRepository->findAll();
        $parents = $this->retrieveParentsCategories($parents);

        if(Request::isPost()) {
            CSRF::validate();
            $params = Request::getAllParams();
            $fields = [];

            if(empty($params['name']) || !Validator::isValidName($params['name']) || !is_null($warehouseCategoryRepository->findOneByName(trim($params['name'])))) {
                $fields['name'] = true;
            }

            if(!empty($fields)) {
                return View::render('Admin/addStocksCategory', ['user' => $user, 'page' => 'add_stocks_category', 'parents' => $parents, 'fields' => $fields, 'params' => $params]);
            }

            $category = new WarehouseCategory();
            $category->setName(htmlspecialchars(trim($params['name'])));
            $category->setParent($warehouseCategoryRepository->find(intval(trim($params['parent']))));

            $em->persist($category);
            $em->flush();

            return $this->redirectTo('/administration/stocks/category');
        }

        return View::render('Admin/addStocksCategory', ['user' => $user, 'page' => 'add_stocks_category', 'parents' => $parents]);
    }

    public function editCategoryAction() {
        Request::assertPostOnly();
        CSRF::validate();

        $userId = $this->logged();
        if(!$userId) {
            return $this->redirectTo('/administration/login');
        }

        $params = Request::getAllParams();

        $em = Entity::getEntityManager();
        $warehouseCategoryRepository = $em->getRepository(WarehouseCategory::class);
        $category = $warehouseCategoryRepository->find($this->getRouteParameter('id'));

        if(is_null($category) || empty($params['name']) || !Validator::isValidName($params['name'])) {
            return $this->redirectTo('/administration/stocks/category');
        }

        $category->setName(htmlspecialchars(trim($params['name'])));
        $category->setParent($warehouseCategoryRepository->find(intval(trim($params['parent']))));

        $em->flush();

        return $this->redirectTo('/administration/stocks/category');
    }

    public function editAction() {
        Request::assertPostOnly();
        CSRF::validate();

        $userId = $this->logged();
        if(!$userId) {
            return $this->redirectTo('/administration/login');
        }

        $params = Request::getAllParams();

        $em = Entity::getEntityManager();
        $warehouseRepository = $em->getRepository(Warehouse::class);
        $warehouseItemRepository = $em->getRepository(WarehouseItem::class);
        $warehouseCategoryRepository = $em->getRepository(WarehouseCategory::class);
        $stock = $warehouseItemRepository->find($this->getRouteParameter('id'));

        $warehouse = $warehouseRepository->find($params['warehouse']);
        if(is_null($warehouse)) {
            return $this->redirectTo('/administration/warehouses');
        }

        if(is_null($stock) || empty($params['name']) || !Validator::isValidName($params['name'])) {
            return $this->redirectTo('/administration/warehouses');
        }

        if(isset($params['category'])) {
            if($params['category'] == 0) {
                $stock->setCategory(null);
            } else if(is_null($stock->getCategory()) || $stock->getCategory()->getId() != $params['category']) {
                $newCategory = $warehouseCategoryRepository->find($params['category']);
                if(!is_null($newCategory)) {
                    $stock->setCategory($newCategory);
                }
            }
        }

        if($params['name'] != $stock->getName()) {
            $stock->setName(htmlspecialchars(trim($params['name'])));
        }

        $em->flush();

        return $this->redirectTo("/administration/stocks/warehouses/" . $warehouse->getId());
    }

    private function retrieveParentsCategories($parents) {
        foreach ($parents as $key => $value) {
            $parents[$key]->parents = [];
            $parent = $value->getParent();
            while ($parent != null) {
                $parents[$key]->parents[] = $parent;
                $parent = $parent->getParent();
            }
            $parents[$key]->parents = array_reverse($parents[$key]->parents);
        }

        return $parents;
    }
}