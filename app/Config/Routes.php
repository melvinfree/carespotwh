<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
// $routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'Home::index');

$routes->post('/Api/Pickpack/scanProduct', '\App\Controllers\Api\Pickpack::scanProduct');
$routes->post('/Api/Pickpack/addBox', '\App\Controllers\Api\Pickpack::addBox');
$routes->post('/Api/Pickpack/getBoxes', '\App\Controllers\Api\Pickpack::getBoxes');
// Orders Api Routes - Endpoints
$routes->post('/Api/Orders/getAll', '\App\Controllers\Api\Orders::getAll');
$routes->post('/Api/Orders/getOrder', '\App\Controllers\Api\Orders::getOrder'); 
$routes->post('/Api/Orders/searchOrder', '\App\Controllers\Api\Orders::searchOrder'); 
$routes->post('/Api/Orders/filterOrder', '\App\Controllers\Api\Orders::filterOrder'); 
$routes->post('/Api/Orders/deleteOrder', '\App\Controllers\Api\Orders::deleteOrder'); 
$routes->post('/Api/Orders/getPlist', '\App\Controllers\Api\Orders::getPlist'); 
$routes->post('/Api/Orders/modifyOrderProduct', '\App\Controllers\Api\Orders::modifyOrderProduct'); 
$routes->post('/Api/Orders/setOrderNotes', '\App\Controllers\Api\Orders::setOrderNotes'); 
$routes->post('/Api/Orders/setOrderStatus', '\App\Controllers\Api\Orders::setOrderStatus'); 

// PurchaseOrders Api Routes - Endpoints

$routes->post('/Api/Purchase/purchaseInit', '\App\Controllers\Api\Purchase::purchaseInit'); 
$routes->post('/Api/Purchase/purchaseList', '\App\Controllers\Api\Purchase::purchaseList'); 
$routes->post('/Api/Purchase/createPurchase', '\App\Controllers\Api\Purchase::createPurchase'); 
$routes->post('/Api/Purchase/getProductsList', '\App\Controllers\Api\Purchase::getProductsList');
$routes->post('/Api/Purchase/searchProduct', '\App\Controllers\Api\Purchase::searchProduct');
$routes->post('/Api/Purchase/addProduct', '\App\Controllers\Api\Purchase::addProduct');
$routes->post('/Api/Purchase/addToStock', '\App\Controllers\Api\Purchase::addToStock');
$routes->post('/Api/Purchase/changeProductInvoice', '\App\Controllers\Api\Purchase::changeProductInvoice');
$routes->post('/Api/Purchase/deleteProduct', '\App\Controllers\Api\Purchase::deleteProduct');
$routes->post('/Api/Purchase/setInvoiceValues', '\App\Controllers\Api\Purchase::setInvoiceValues');
$routes->post('/Api/Purchase/getInvoicePList', '\App\Controllers\Api\Purchase::getInvoicePList');
$routes->post('/Api/Purchase/getPurchase', '\App\Controllers\Api\Purchase::getPurchase');
$routes->post('/Api/Purchase/lockInvoice', '\App\Controllers\Api\Purchase::lockInvoice');
// Inventory Api Routes - Endpoints

$routes->post('/Api/Inventory/getReceptionsList', '\App\Controllers\Api\Inventory::getReceptionsList');
$routes->post('/Api/Inventory/getProductsList', '\App\Controllers\Api\Inventory::getProductsList');
$routes->post('/Api/Inventory/getNotConfirmedProductPcs', '\App\Controllers\Api\Inventory::getNotConfirmedProductPcs');
$routes->post('/Api/Inventory/addProductInventory', '\App\Controllers\Api\Inventory::addProductInventory');
$routes->post('/Api/Inventory/ProductsStock', '\App\Controllers\Api\Inventory::ProductsStock');

// Inventory Transfers (between warehouses)

$routes->post('/Api/Transfers/transferInit', '\App\Controllers\Api\Transfers::transferInit');
$routes->post('/Api/Transfers/createTransfer', '\App\Controllers\Api\Transfers::createTransfer');
$routes->post('/Api/Transfers/transfersList', '\App\Controllers\Api\Transfers::transfersList');
$routes->post('/Api/Transfers/searchProduct', '\App\Controllers\Api\Transfers::searchProduct');
$routes->post('/Api/Transfers/addProduct', '\App\Controllers\Api\Transfers::addProduct');
$routes->post('/Api/Transfers/getTransfer', '\App\Controllers\Api\Transfers::getTransfer');
$routes->post('/Api/Transfers/addToTransfer', '\App\Controllers\Api\Transfers::addToTransfer');
$routes->post('/Api/Transfers/getTransferProductsList', '\App\Controllers\Api\Transfers::getTransferProductsList');
$routes->post('/Api/Transfers/addProductTransfer', '\App\Controllers\Api\Transfers::addProductTransfer');

// Products


$routes->post('/Api/Products/getProductsList', '\App\Controllers\Api\Products::getProductsList');
$routes->post('/Api/Products/getProduct', '\App\Controllers\Api\Products::getProduct');
$routes->post('/Api/Products/deleteEan', '\App\Controllers\Api\Products::deleteEan');
$routes->post('/Api/Products/addEan', '\App\Controllers\Api\Products::addEan');

// GENERAL

//warehouses
$routes->post('/Api/Warehouse/warehouseList', '\App\Controllers\Api\Warehouse::warehouseList');
$routes->post('/Api/Warehouse/getWarehouse', '\App\Controllers\Api\Warehouse::getWarehouse');
$routes->post('/Api/Warehouse/deleteWarehouse', '\App\Controllers\Api\Warehouse::deleteWarehouse');
$routes->post('/Api/Warehouse/setWarehouseInfo', '\App\Controllers\Api\Warehouse::setWarehouseInfo');


// PickPack Module



/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
