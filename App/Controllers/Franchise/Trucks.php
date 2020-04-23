<?php

namespace App\Controllers\Franchise;

use App\Entity\Invoice;
use App\Entity\InvoiceLine;
use App\Entity\Maintenance;
use App\Entity\MaintenanceInfo;
use App\Entity\Users;
use App\Entity\Warehouse;
use App\Entity\WarehouseItem;
use App\Entity\WarehouseStockItem;
use Core\Controller;
use Core\CSRF;
use Core\Entity;
use Core\Request;
use Core\Session;
use Core\View;

class Trucks extends Controller {

    public function indexAction() {
        $userId = $this->logged();
        if(!$userId) {
            return $this->redirectTo('/panel/login');
        }

        $em = Entity::getEntityManager();
        $user = $em->find(Users::class, $userId);

        $truck = $user->getTruck();
        if(is_null($truck)) {
            return $this->noTruckPage();
        }

        $maintenanceRepository = $em->getRepository(Maintenance::class);
        $maintenances = $maintenanceRepository->findByTruck($truck);

        $activeMaintenance = $maintenanceRepository->findOneBy(['truck' => $truck, 'status' => ['processing', 'waiting']]);

        return View::render('Franchise/truck', ['user' => $user, 'page' => 'truck', 'truck' => $truck, 'maintenances' => $maintenances, 'activeMaintenance' => $activeMaintenance]);
    }

    public function maintenanceAction() {
        $userId = $this->logged();
        if(!$userId) {
            return $this->redirectTo('/panel/login');
        }

        $em = Entity::getEntityManager();
        $user = $em->find(Users::class, $userId);

        $truck = $user->getTruck();
        if(is_null($truck)) {
            return $this->noTruckPage();
        }

        $maintenanceRepository = $em->getRepository(Maintenance::class);
        $maintenanceInfoRepository = $em->getRepository(MaintenanceInfo::class);
        $maintenance = $maintenanceRepository->find($this->getRouteParameter('id'));
        $data = $maintenanceInfoRepository->findByMaintenance($maintenance);
        $data = array_reverse($data);

        $infos = [];
        foreach ($data as $item) {
            $infos[$item->getCreatedAt()->format('d/m/Y')][] = $item;
        }

        return View::render('Franchise/maintenance', ['user' => $user, 'page' => 'truck', 'infos' => $infos, 'maintenance' => $maintenance, 'truck' => $truck]);
    }

    public function supplyAction() {
        $userId = $this->logged();
        if(!$userId) {
            return $this->redirectTo('/panel/login');
        }

        $em = Entity::getEntityManager();
        $user = $em->find(Users::class, $userId);
        $truck = $user->getTruck();
        if(is_null($truck)) {
            return $this->noTruckPage();
        }
        $warehouseRepository = $em->getRepository(Warehouse::class);
        $warehouses = $warehouseRepository->findAll();

        return View::render('Franchise/supply', ['user' => $user, 'page' => 'truck_supply', 'warehouses' => $warehouses]);
    }

    public function supplyStepTwoAction() {
        $userId = $this->logged();
        if(!$userId) {
            return $this->redirectTo('/panel/login');
        }

        CSRF::generate();
        $em = Entity::getEntityManager();
        $user = $em->find(Users::class, $userId);

        $warehouseRepository = $em->getRepository(Warehouse::class);
        $warehouseStockItemRepository = $em->getRepository(WarehouseStockItem::class);
        $warehouseItemRepository = $em->getRepository(WarehouseItem::class);
        $warehouse = $warehouseRepository->find($this->getRouteParameter('id'));
        if(is_null($warehouse)) {
            return $this->redirectTo('/panel/supply');
        }

        $allProducts = $warehouseItemRepository->findAll();

        foreach ($allProducts as $key => $value) {
            $products = $warehouseStockItemRepository->findBy(['item' => $value, 'warehouse' => $warehouse]);
            $allProducts[$key]->total = 0;
            foreach ($products as $item) {
                $allProducts[$key]->total += $item->getQuantity();
            }
        }

        return View::render('Franchise/supplyStepTwo', ['user' => $user, 'page' => 'truck_supply', 'warehouse' => $warehouse, 'products' => $allProducts]);
    }

    public function supplyStepThreeAction() {
        Request::assertPostOnly();
        CSRF::validate();

        $userId = $this->logged();
        if(!$userId) {
            return $this->redirectTo('/panel/login');
        }

        CSRF::generate();
        $em = Entity::getEntityManager();
        $user = $em->find(Users::class, $userId);
        $truck = $user->getTruck();
        if(is_null($truck)) {
            return $this->noTruckPage();
        }
        $params = Request::getAllParams();

        $warehouseRepository = $em->getRepository(Warehouse::class);
        $warehouseStockItemRepository = $em->getRepository(WarehouseStockItem::class);
        $warehouseItemRepository = $em->getRepository(WarehouseItem::class);
        $warehouse = $warehouseRepository->find($this->getRouteParameter('id'));
        if(is_null($warehouse)) {
            return $this->redirectTo('/panel/supply');
        }

        $products = [];
        foreach ($params['products'] as $productId) {
            $product = $warehouseItemRepository->find($productId);
            $product->max = $warehouseStockItemRepository->findOneBy(['warehouse' => $warehouse, 'item' => $product])->getQuantity();
            if(!is_null($product)) {
                $products[] = $product;
            }
        }

        if(empty($products)) {
            return $this->redirectTo('/panel/truck/supply');
        }

        return View::render('Franchise/supplyStepThree', ['user' => $user, 'page' => 'truck_supply', 'warehouse' => $warehouse, 'products' => $products]);
    }

    public function supplyStepFourAction() {
        Request::assertPostOnly();
        CSRF::validate();

        $userId = $this->logged();
        if(!$userId) {
            return $this->redirectTo('/panel/login');
        }

        CSRF::generate();
        $em = Entity::getEntityManager();
        $user = $em->find(Users::class, $userId);
        $truck = $user->getTruck();
        if(is_null($truck)) {
            return $this->noTruckPage();
        }
        $params = Request::getAllParams();
        $warehouseRepository = $em->getRepository(Warehouse::class);
        $warehouseStockItemRepository = $em->getRepository(WarehouseStockItem::class);
        $warehouseItemRepository = $em->getRepository(WarehouseItem::class);
        $warehouse = $warehouseRepository->find($this->getRouteParameter('id'));
        if(is_null($warehouse)) {
            return $this->redirectTo('/panel/supply');
        }

        $invoice = new Invoice();
        $invoice->setRecipient($user->getSocietyName());
        $invoice->setOwner('drivncook');
        $invoice->setWarehouse($warehouse);
        $invoice->setUser($user);

        $em->persist($invoice);
        $em->flush();

        $productsId = $params['product_id'];
        foreach ($productsId as $key => $productId) {
            $product = $warehouseItemRepository->find($productId);
            $itemStock = $warehouseStockItemRepository->findOneBy(['warehouse' => $warehouse, 'item' => $product]);
            $selectedQuantity = intval($params['quantity'][$key]);

            if(is_null($itemStock) || $selectedQuantity <= 0) continue;

            if($selectedQuantity > $itemStock->getQuantity()) {
                $selectedQuantity = $itemStock->getQuantity();
            }

            $itemStock->setQuantity($itemStock->getQuantity() - $selectedQuantity);

            $invoiceLine = new InvoiceLine();
            $invoiceLine->setInvoice($invoice);
            $invoiceLine->setText($product->getName());
            $invoiceLine->setQuantity($selectedQuantity);
            $invoiceLine->setPrice($product->getResalePrice());
            $invoiceLine->setTva($product->getTva());

            $em->persist($invoiceLine);
            $em->flush();
        }

        return $this->redirectTo('/panel/invoices/' . $invoice->getId());
    }

    protected function logged() {
        return Session::get('user_id');
    }

    private function noTruckPage() {
        $userId = $this->logged();
        if(!$userId) {
            return $this->redirectTo('/panel/login');
        }

        $em = Entity::getEntityManager();
        $user = $em->find(Users::class, $userId);

        return View::render('Franchise/noTruck', ['user' => $user]);
    }
}