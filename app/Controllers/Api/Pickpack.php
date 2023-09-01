<?php

namespace App\Controllers\Api;

use CodeIgniter\API\ResponseTrait;
use App\Models\Api\Inventory\ProductsModel;
use CodeIgniter\Controller;
use App\Models\Api\PurchaseOrder\PurchaseOrderModel;
use App\Models\Api\PurchaseOrder\PurchaseOrderProductModel;
use App\Models\Api\Inventory\ReceptionsModel;
use App\Models\Api\Inventory\TransfersModel;
use App\Models\Api\Inventory\StockModel;
use App\Models\Api\Scanning\OrderBoxProductsModel;
use DateTime;

class Pickpack extends Controller
{
    use ResponseTrait;

    public function __construct()
    {
        helper("api");
        helper("currency");
    }


    public function scanProduct() {

        $OrderBoxProductsModel = new OrderBoxProductsModel();
    
        // Validate token and get the request body
        try {
            $requestData = validateTokenAndFetchData();
        } catch (\Exception $e) {
            return $this->failUnauthorized($e->getMessage());
        }


        $totalExecutions = 1;


        $responses = [];

        $counter = 0;

        while ($counter < $totalExecutions) {
            $responses[] = $OrderBoxProductsModel->processProduct($requestData);
    
            $counter++;
        }
    
        return $this->respond(['responses' => "test"], 200); 

    }

}