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

// Orders Api Routes - Endpoints
$routes->post('/Api/Orders/getAll', '\App\Controllers\Api\Orders::getAll');
$routes->post('/Api/Orders/getOrder', '\App\Controllers\Api\Orders::getOrder'); 
$routes->post('/Api/Orders/searchOrder', '\App\Controllers\Api\Orders::searchOrder'); 
$routes->post('/Api/Orders/filterOrder', '\App\Controllers\Api\Orders::filterOrder'); 
$routes->post('/Api/Orders/deleteOrder', '\App\Controllers\Api\Orders::deleteOrder'); 
$routes->post('/Api/Orders/getPlist', '\App\Controllers\Api\Orders::getPlist'); 

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
