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
use App\Models\Api\Inventory\WarehouseModel;
use DateTime;

class Warehouse extends Controller
{
    use ResponseTrait;

    public function __construct()
    {
        helper("api");
        helper("currency");
    }

    public function warehouseList()
    {
        $WarehouseModel = new WarehouseModel();

        // Validate token and get the request body
        try {
            $requestData = validateTokenAndFetchData();
        } catch (\Exception $e) {
            return $this->failUnauthorized($e->getMessage());
        }

        // Validate the JSON payload
        $expectedKeys = ["limit", "offset"];
        $requestDataKeys = array_keys($requestData);

        if (
            count($requestData) !== 2 ||
            !empty(array_diff($expectedKeys, $requestDataKeys))
        ) {
            return $this->fail("Invalid JSON Payload, check APIDocs.", 400);
        }
        if (!is_int($requestData["limit"]) || !is_int($requestData["offset"])) {
            return $this->fail("Invalid JSON Payload, check APIDocs.", 400);
        }

        
        $jsonRes = json_encode(succesResponse($WarehouseModel->whList($requestData["limit"], $requestData["offset"])), true);

        return $this->respond($jsonRes, 200);
    }
    
    // ALLOWED FIELDS
    /* MANDATORY: warehouse_id

    */
    public function getWarehouse()
    {
        $WarehouseModel = new WarehouseModel();

        // Validate token and get the request body
        try {
            $requestData = validateTokenAndFetchData();
        } catch (\Exception $e) {
            return $this->failUnauthorized($e->getMessage());
        }

        // Validate the JSON payload
        $expectedKeys = ["warehouse_id"];
        $requestDataKeys = array_keys($requestData);

        if (
            count($requestData) !== 1 || !empty(array_diff($expectedKeys, $requestDataKeys))
        ) {
            return $this->fail("Invalid JSON Payload, check APIDocs.", 400);
        }
        if (!is_int($requestData["warehouse_id"])) {
            return $this->fail("Invalid JSON Payload, check APIDocs.", 400);
        }

        
        
        $results = $WarehouseModel->getWHInfo($requestData["warehouse_id"]);

        if ($results) {
            $jsonRes = json_encode(succesResponse($results), true);
            return $this->respond($jsonRes, 200);
        } else {
            return $this->failNotFound('No Transfer found with ID: '.$requestData["warehouse_id"]);
        }

    }
    // ALLOWED FIELDS
    /* MANDATORY: warehouse_id

    */
    public function deleteWarehouse()
    {
    $WarehouseModel = new WarehouseModel();

    // Validate token and get the request body
    try {
        $requestData = validateTokenAndFetchData();
    } catch (\Exception $e) {
        return $this->failUnauthorized($e->getMessage());
    }
    $responses = $WarehouseModel->deleteWH($requestData['warehouse_id']);

    $jsonRes = json_encode(succesResponse($responses), true);

    return $this->respond($jsonRes, 200);
    }
    

    // ALLOWED FIELDS
    /*
    MANDATORY: "warehouse_id"
    "name", 
    "comments", 
    "status", 
    "allow_servicing_stock", 
    "open_box", 
    "location_id"
    */
    public function setWarehouseInfo()
    {
    $WarehouseModel = new WarehouseModel();

    // Validate token and get the request body
    try {
        $requestData = validateTokenAndFetchData();
    } catch (\Exception $e) {
        return $this->failUnauthorized($e->getMessage());
    }
    $responses = $WarehouseModel->modifyWarehouse($requestData);

    $jsonRes = json_encode(succesResponse($responses), true);

    return $this->respond($jsonRes, 200);
    }

}