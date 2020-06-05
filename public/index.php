<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);
session_start();
require dirname(__DIR__) . '/vendor/autoload.php';
error_reporting(E_ALL);
set_error_handler('Core\Error::errorHandler');
set_exception_handler('Core\Error::exceptionHandler');
$router = new Core\Router();

/**
 * Routes
 */
$router->add('', ['controller' => 'Home', 'action' => 'index', 'namespace' => 'Home']);
$router->add('lang/pref/{lang:(?:[\w\-](?<!_))+}', ['controller' => 'Home', 'action' => 'lang', 'namespace' => 'Home']);

/**
 * Administration
 */
$router->add("administration", ['controller' => 'Home', 'action' => 'index', 'namespace' => 'Admin']);
$router->add("administration/login", ['controller' => 'Home', 'action' => 'login', 'namespace' => 'Admin']);
$router->add("administration/logout", ['controller' => 'Home', 'action' => 'logout', 'namespace' => 'Admin']);
// Admin management
$router->add("administration/administrators", ['controller' => 'Admins', 'action' => 'administrators', 'namespace' => 'Admin']);
$router->add("administration/administrators/add", ['controller' => 'Admins', 'action' => 'addAdministrators', 'namespace' => 'Admin']);
$router->add("administration/administrators/{id:\d+}/edit", ['controller' => 'Admins', 'action' => 'editAdministrators', 'namespace' => 'Admin']);
$router->add("administration/administrators/{id:\d+}/delete", ['controller' => 'Admins', 'action' => 'deleteAdministrators', 'namespace' => 'Admin']);
// Trucks management
$router->add("administration/trucks", ['controller' => 'Trucks', 'action' => 'trucks', 'namespace' => 'Admin']);
$router->add("administration/trucks/add", ['controller' => 'Trucks', 'action' => 'addTrucks', 'namespace' => 'Admin']);
$router->add("administration/trucks/assign/{id:\d+}", ['controller' => 'Trucks', 'action' => 'assign', 'namespace' => 'Admin']);
$router->add("administration/trucks/{id:\d+}/informations", ['controller' => 'Trucks', 'action' => 'informations', 'namespace' => 'Admin']);
$router->add("administration/trucks/{id:\d+}/maintenance", ['controller' => 'Trucks', 'action' => 'setMaintenance', 'namespace' => 'Admin']);
$router->add("administration/trucks/{id:\d+}/maintenance/{mid:\d+}", ['controller' => 'Trucks', 'action' => 'maintenanceDetails', 'namespace' => 'Admin']);
$router->add("administration/trucks/{id:\d+}/maintenance/{mid:\d+}/status", ['controller' => 'Trucks', 'action' => 'maintenanceStatus', 'namespace' => 'Admin']);
$router->add("administration/trucks/assign/remove/{id:\d+}", ['controller' => 'Trucks', 'action' => 'removeAssign', 'namespace' => 'Admin']);
$router->add("administration/trucks/active/{id:\d+}", ['controller' => 'Trucks', 'action' => 'setActive', 'namespace' => 'Admin']);
$router->add("administration/trucks/inactive/{id:\d+}", ['controller' => 'Trucks', 'action' => 'setInactive', 'namespace' => 'Admin']);
// Franchise management
$router->add("administration/franchises", ['controller' => 'Franchises', 'action' => 'franchises', 'namespace' => 'Admin']);
$router->add("administration/franchises/add", ['controller' => 'Franchises', 'action' => 'addFranchises', 'namespace' => 'Admin']);
$router->add("administration/franchises/shellInsert", ['controller' => 'Franchises', 'action' => 'shellInsert', 'namespace' => 'Admin']);
$router->add("administration/franchises/shellCheck", ['controller' => 'Franchises', 'action' => 'shellCheck', 'namespace' => 'Admin']);
$router->add("administration/franchises/{id:\d+}/edit", ['controller' => 'Franchises', 'action' => 'edit', 'namespace' => 'Admin']);
$router->add("administration/franchises/{id:\d+}/block", ['controller' => 'Franchises', 'action' => 'block', 'namespace' => 'Admin']);
$router->add("administration/franchises/{id:\d+}/delete", ['controller' => 'Franchises', 'action' => 'delete', 'namespace' => 'Admin']);
// Entrepôts
$router->add("administration/warehouses", ['controller' => 'Warehouses', 'action' => 'warehouses', 'namespace' => 'Admin']);
$router->add("administration/warehouses/add", ['controller' => 'Warehouses', 'action' => 'addWarehouses', 'namespace' => 'Admin']);
$router->add("administration/warehouses/{id:\d+}/edit", ['controller' => 'Warehouses', 'action' => 'edit', 'namespace' => 'Admin']);
$router->add("administration/warehouses/{id:\d+}/supply", ['controller' => 'Warehouses', 'action' => 'supply', 'namespace' => 'Admin']);
$router->add("administration/warehouses/{id:\d+}/supply/{product:\d+}", ['controller' => 'Warehouses', 'action' => 'supply', 'namespace' => 'Admin']);
$router->add("administration/warehouses/{id:\d+}/supply/step/2", ['controller' => 'Warehouses', 'action' => 'supplyStepTwo', 'namespace' => 'Admin']);
$router->add("administration/warehouses/{id:\d+}/supply/step/3", ['controller' => 'Warehouses', 'action' => 'supplyStepThree', 'namespace' => 'Admin']);
// Stocks
$router->add("administration/stocks/warehouses/{id:\d+}", ['controller' => 'Stocks', 'action' => 'stocks', 'namespace' => 'Admin']);
$router->add("administration/stocks/edit/{id:\d+}", ['controller' => 'Stocks', 'action' => 'edit', 'namespace' => 'Admin']);
$router->add("administration/stocks/category", ['controller' => 'Stocks', 'action' => 'stocksCategories', 'namespace' => 'Admin']);
$router->add("administration/stocks/category/{id:\d+}/edit", ['controller' => 'Stocks', 'action' => 'editCategory', 'namespace' => 'Admin']);
$router->add("administration/stocks/category/add", ['controller' => 'Stocks', 'action' => 'addCategory', 'namespace' => 'Admin']);
// Factures
$router->add("administration/invoices/received", ['controller' => 'Invoices', 'action' => 'received', 'namespace' => 'Admin']);
$router->add("administration/invoices/issued", ['controller' => 'Invoices', 'action' => 'issued', 'namespace' => 'Admin']);
$router->add("administration/invoices/add/{id:\d+}", ['controller' => 'Invoices', 'action' => 'add', 'namespace' => 'Admin']);
$router->add("administration/invoices/{id:\d+}/delete", ['controller' => 'Invoices', 'action' => 'deleteRealInvoice', 'namespace' => 'Admin']);
$router->add("administration/invoices/real/{id:\d+}", ['controller' => 'Invoices', 'action' => 'real', 'namespace' => 'Admin']);
$router->add("administration/invoices/{id:\d+}", ['controller' => 'Invoices', 'action' => 'see', 'namespace' => 'Admin']);
$router->add("administration/invoices/{id:\d+}/pay", ['controller' => 'Invoices', 'action' => 'pay', 'namespace' => 'Admin']);

/**
 * Panel franchisé
 */
$router->add("panel", ['controller' => 'Home', 'action' => 'index', 'namespace' => 'Franchise']);
$router->add("panel/login", ['controller' => 'Home', 'action' => 'login', 'namespace' => 'Franchise']);
$router->add("panel/logout", ['controller' => 'Home', 'action' => 'logout', 'namespace' => 'Franchise']);
// Gestion compte
$router->add("panel/my-account", ['controller' => 'Franchise', 'action' => 'myAccount', 'namespace' => 'Franchise']);
$router->add("panel/online-payment", ['controller' => 'Franchise', 'action' => 'onlinePayment', 'namespace' => 'Franchise']);
// Camion
$router->add("panel/truck", ['controller' => 'Trucks', 'action' => 'index', 'namespace' => 'Franchise']);
$router->add("panel/truck/maintenance/{id:\d+}", ['controller' => 'Trucks', 'action' => 'maintenance', 'namespace' => 'Franchise']);
$router->add("panel/truck/supply", ['controller' => 'Trucks', 'action' => 'supply', 'namespace' => 'Franchise']);
$router->add("panel/truck/supply/warehouse/{id:\d+}", ['controller' => 'Trucks', 'action' => 'supplyStepTwo', 'namespace' => 'Franchise']);
$router->add("panel/truck/supply/warehouse/{id:\d+}/step/3", ['controller' => 'Trucks', 'action' => 'supplyStepThree', 'namespace' => 'Franchise']);
$router->add("panel/truck/supply/warehouse/{id:\d+}/step/4", ['controller' => 'Trucks', 'action' => 'supplyStepFour', 'namespace' => 'Franchise']);
// Factures
$router->add("panel/invoices/received", ['controller' => 'Invoices', 'action' => 'received', 'namespace' => 'Franchise']);
$router->add("panel/invoices/{id:\d+}", ['controller' => 'Invoices', 'action' => 'see', 'namespace' => 'Franchise']);
$router->add("panel/invoices/pay/{id:\d+}", ['controller' => 'Invoices', 'action' => 'payInvoice', 'namespace' => 'Franchise']);

// Card
$router->add("panel/card", ['controller' => 'Cards', 'action' => 'index', 'namespace' => 'Franchise']);
$router->add("panel/card/add", ['controller' => 'Cards', 'action' => 'add', 'namespace' => 'Franchise']);
$router->add("panel/card/edit/{id:\d+}", ['controller' => 'Cards', 'action' => 'edit', 'namespace' => 'Franchise']);
$router->add("panel/card/delete/{id:\d+}", ['controller' => 'Cards', 'action' => 'delete', 'namespace' => 'Franchise']);
$router->add("panel/card/deletecategory/{id:\d+}", ['controller' => 'Cards', 'action' => 'deleteCategory', 'namespace' => 'Franchise']);
$router->add("panel/card/deleteitem/{id:\d+}", ['controller' => 'Cards', 'action' => 'deleteItem', 'namespace' => 'Franchise']);


$router->dispatch($_SERVER['QUERY_STRING']);
