<?php

namespace App\Controllers\Admin;

use App\Entity\Admin;
use App\Entity\Invoice;
use App\Entity\InvoiceLine;
use App\Entity\Warehouse;
use App\Entity\WarehouseItem;
use App\Entity\WarehouseStockItem;
use Core\Controller;
use Core\CSRF;
use Core\Entity;
use Core\Request;
use Core\Session;
use Core\Validator;
use Core\View;

class Warehouses extends Controller {

    private $admin;

    public function __construct($routes)
    {
        parent::__construct($routes);

        $userId = Session::get('admin_id');
        $em = Entity::getEntityManager();
        if(is_null($admin = $em->find(Admin::class, $userId))) {
            return $this->redirectTo('/administration/login');
        }

        $this->admin = $admin;
    }

    public function warehousesAction() {
        $em = Entity::getEntityManager();

        $warehousesRepository = $em->getRepository(Warehouse::class);
        $warehouseStockItemRepository = $em->getRepository(WarehouseStockItem::class);

        $warehouses = $warehousesRepository->findAll();
        foreach ($warehouses as $key => $warehouse) {
            $products = $warehouseStockItemRepository->findByWarehouse($warehouse);
            $warehouses[$key]->total = count($products);
            $warehouses[$key]->quantityTotal = 0;
            foreach ($products as $item) {
                $warehouses[$key]->quantityTotal += $item->getQuantity();
            }
        }

        return View::render('Admin/warehouses', ['user' => $this->admin, 'page' => 'warehouses', 'warehouses' => $warehouses]);
    }


    public function supplyAction() {
        $em = Entity::getEntityManager();

        $warehouseRepository = $em->getRepository(Warehouse::class);
        $warehouseItemRepository = $em->getRepository(WarehouseItem::class);
        $warehouseStockItemRepository = $em->getRepository(WarehouseStockItem::class);

        CSRF::generate();

        if(is_null($warehouse = $warehouseRepository->find($this->getRouteParameter('id')))) {
            return $this->redirectTo('/administration/warehouses');
        }

        $product = $warehouseItemRepository->find($this->getRouteParameter('product'));
        $allProducts = $warehouseItemRepository->findAll();

        // Récupère la quantité restante d'un produit dans un entrepôt
        foreach ($allProducts as $key => $value) {
            $products = $warehouseStockItemRepository->findBy(['item' => $value, 'warehouse' => $warehouse]);
            $allProducts[$key]->total = 0;
            foreach ($products as $item) {
                $allProducts[$key]->total += $item->getQuantity();
            }
        }

        return View::render('Admin/supply', ['user' => $this->admin, 'warehouse' => $warehouse, 'product' => $product, 'page' => 'warehouses', 'products' => $allProducts]);
    }

    public function supplyStepTwo() {
        Request::assertPostOnly();
        CSRF::validate();

        $em = Entity::getEntityManager();

        $warehouseRepository = $em->getRepository(Warehouse::class);
        $warehouseItemRepository = $em->getRepository(WarehouseItem::class);

        CSRF::generate();

        if(is_null($warehouse = $warehouseRepository->find($this->getRouteParameter('id')))) {
            return $this->redirectTo('/administration/warehouses');
        }

        $params = Request::getAllParams();

        if(isset($params['products'])) {
            $selectedProducts = array_unique($params['products']);
        } else {
            $selectedProducts = [];
        }

        if(isset($params['new_products']) && trim($params['new_products']) != "") {
            $newProducts = explode(',', trim($params['new_products']));
        } else {
            $newProducts = [];
        }

        if(empty($selectedProducts) && empty($newProducts)) {
            return $this->redirectTo('/administration/warehouses/' . $warehouse->getId() . '/supply');
        }

        $products = [];

        foreach ($selectedProducts as $productId) {
            $product = $warehouseItemRepository->find($productId);
            if(!is_null($product)) {
                $products[] = $product;
            }
        }

        foreach ($newProducts as $key) {
            $products[] = ['name' => htmlspecialchars(trim($key))];
        }

        return View::render('Admin/supplyStepTwo', ['user' => $this->admin, 'page' => 'warehouses', 'warehouse' => $warehouse, 'products' => $products]);
    }

    public function supplyStepThree(){
        Request::assertPostOnly();
        CSRF::validate();

        $em = Entity::getEntityManager();

        $warehouseRepository = $em->getRepository(Warehouse::class);
        $warehouseItemRepository = $em->getRepository(WarehouseItem::class);
        $warehouseStockItemRepository = $em->getRepository(WarehouseStockItem::class);

        CSRF::generate();

        if(is_null($warehouse = $warehouseRepository->find($this->getRouteParameter('id')))) {
            return $this->redirectTo('/administration/warehouses');
        }

        $params = Request::getAllParams();

        if(
            empty($params['name']) ||
            empty($params['price']) ||
            empty($params['resale_price']) ||
            empty($params['quantity']) ||
            empty($params['type']) ||
            empty($params['provider'])
        ) {
            return $this->redirectTo('/administration/warehouses');
        }

        $names = $params['name'];
        $names = array_map('trim', $names);
        $names = array_map('htmlspecialchars', $names);

        $prices = $params['price'];
        $prices = array_map('floatval', $prices);

        $resalePrices = $params['resale_price'];
        $resalePrices = array_map('floatval', $resalePrices);

        $tva = $params['tva'];
        $tva = array_map('floatval', $tva);

        $quantities = $params['quantity'];
        $quantities = array_map('round', $quantities);

        $types = $params['type'];
        $productsId = $params['product_id'];

        if(!empty($params['provider'])) {
            $provider = htmlspecialchars(trim($params['provider']));
        } else {
            $provider = "n/A";
        }

        $invoice = new Invoice();
        $invoice->setWarehouse($warehouse);
        $invoice->setRecipient('drivncook');
        $invoice->setOwner($provider);
        $invoice->setStatus(false);

        if(!empty($params['address_line'])) {
            $invoice->setOwnerAddressLine(htmlspecialchars(trim($params['address_line'])));
        }

        if(!empty($params['postal_code']) && Validator::isValidPostalCode($params['postal_code'])) {
            $invoice->setOwnerPostalCode(htmlspecialchars(trim($params['postal_code'])));
        }

        if(!empty($params['city'])) {
            $invoice->setOwnerCity(htmlspecialchars(trim($params['city'])));
        }

        $em->persist($invoice);
        $em->flush();

        foreach ($productsId as $key => $id) {
            if($types[$key]) {
                $item = $warehouseItemRepository->find($id);

            } else {
                $item = new WarehouseItem();
                $avoidDuplicate = $warehouseItemRepository->findOneByName($names[$key]);
                if(!is_null($avoidDuplicate)) {
                    $item = $avoidDuplicate;
                    $types[$key] = 1;
                }
            }

            $item->setName(htmlspecialchars(trim($names[$key])));
            $item->setPrice($prices[$key]);
            $item->setTva($tva[$key]);
            $item->setResalePrice($resalePrices[$key]);

            if($types[$key] == 0) {
                $em->persist($item);
            }

            $em->flush();

            $stockItem = $warehouseStockItemRepository->findOneBy(['item' => $item, 'warehouse' => $warehouse]);
            if(!is_null($stockItem)) {
                $stockItem->setQuantity($stockItem->getQuantity() + $quantities[$key]);
            } else {
                $stockItem = new WarehouseStockItem();
                $stockItem->setItem($item);
                $stockItem->setWarehouse($warehouse);
                $stockItem->setQuantity($quantities[$key]);

                $em->persist($stockItem);
            }

            $em->flush();

            // Génération des lignes de l'invoice
            $invoiceLine = new InvoiceLine();
            $invoiceLine->setInvoice($invoice);
            $invoiceLine->setText($names[$key]);
            $invoiceLine->setQuantity($quantities[$key]);
            $invoiceLine->setPrice($prices[$key]);
            $invoiceLine->setTva($tva[$key]);

            $em->persist($invoiceLine);
            $em->flush();
        }

        return $this->redirectTo('/administration/invoices/' . $invoice->getId());
    }

    public function addWarehousesAction() {
        $em = Entity::getEntityManager();

        CSRF::generate();
        if(Request::isPost()) {
            CSRF::validate();
            $params = Request::getAllParams();
            $fields = [];

            if(empty($params['name'] || !Validator::isValidName($params['name']))) {
                $fields['name'] = true;
            }

            if(empty($params['location']) || strlen($params['location']) <= 3) {
                $fields['location'] = true;
            }

            if(!empty($fields)) {
                return View::render('Admin/addWarehouses', ['user' => $this->admin, 'page' => 'add_warehouses', 'fields' => $fields, 'params' => $params]);
            }

            $warehouse = new Warehouse();
            $warehouse->setName(htmlspecialchars(trim($params['name'])));
            $warehouse->setLocation(htmlspecialchars(trim($params['location'])));

            $em->persist($warehouse);
            $em->flush();

            return $this->redirectTo('/administration/warehouses');
        }

        return View::render('Admin/addWarehouses', ['user' => $this->admin, 'page' => 'add_warehouses']);
    }

    public function editAction() {
        Request::assertPostOnly();
        CSRF::validate();

        $params = Request::getAllParams();
        
        $em = Entity::getEntityManager();

        $warehouseRepository = $em->getRepository(Warehouse::class);

        if(is_null($warehouse = $warehouseRepository->find($this->getRouteParameter('id')))) {
            return $this->redirectTo('/administration/warehouses');
        }

        if(!empty($params['name']) && $params['name'] != $warehouse->getName() && Validator::isValidName($params['name'])) {
            $warehouse->setName(htmlspecialchars(trim($params['name'])));
        }

        if(!empty($params['location']) && $params['location'] != $warehouse->getLocation() && strlen($params['location']) > 3) {
            $warehouse->setLocation(htmlspecialchars(trim($params['location'])));
        }

        $em->flush();
        return $this->redirectTo('/administration/warehouses');
    }

}