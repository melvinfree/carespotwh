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
use App\Models\Api\Scanning\OrderBoxesModel;

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
    
        return $this->respond(['responses' => $responses], 200); 

    }

    public function addBox() {

        $OrderBoxesModel = new OrderBoxesModel();
    
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
            $responses[] = $OrderBoxesModel->addBox($requestData);
    
            $counter++;
        }
    
        return $this->respond(['responses' => $responses], 200); 

    }

    public function getBoxes()
    {
        $OrderBoxesModel = new OrderBoxesModel();

        // Validate token and get the request body
        try {
            $requestData = validateTokenAndFetchData();
        } catch (\Exception $e) {
            return $this->failUnauthorized($e->getMessage());
        }
        
        $results = $OrderBoxesModel->getBoxList($requestData);

        if ($results) {
            $jsonRes = json_encode(succesResponse($results), true);
            return $this->respond($jsonRes, 200);
        } else {
            return $this->failNotFound('No boxes found with ID');
        }

    }

    public function deleteBox()
    {
        $OrderBoxesModel = new OrderBoxesModel();

        // Validate token and get the request body
        try {
            $requestData = validateTokenAndFetchData();
        } catch (\Exception $e) {
            return $this->failUnauthorized($e->getMessage());
        }

        if (!isset($requestData["box_id"])) {
            return $this->failValidationError("Missing mandatory field: id");
        }

        // Delete the order from the database
        $results = $OrderBoxesModel->deleteBox($requestData["box_id"]);

        $jsonRes = json_encode($results, true);

        // Return a success message
        return $this->respondDeleted($jsonRes);
    }

}