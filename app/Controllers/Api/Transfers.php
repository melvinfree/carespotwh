<?php

namespace App\Controllers\Api;

use CodeIgniter\API\ResponseTrait;
use App\Models\Api\Inventory\ProductsModel;
use CodeIgniter\Controller;
use App\Models\Api\PurchaseOrder\PurchaseOrderModel;
use App\Models\Api\PurchaseOrder\PurchaseOrderProductModel;
use App\Models\Api\Inventory\ReceptionsModel;
use App\Models\Api\Inventory\TransfersModel;
use App\Models\Api\Inventory\TransferProductModel;
use DateTime;

class Transfers extends Controller
{
    use ResponseTrait;

    public function __construct()
    {
        helper("api");
        helper("currency");
    }

    public function transferInit()
    {
        $Transfer = new TransfersModel();

        // Validate token and get the request body
        try {
            $requestData = validateTokenAndFetchData();
        } catch (\Exception $e) {
            return $this->failUnauthorized($e->getMessage());
        }

        $results = $Transfer->getInfo();

        $jsonRes = json_encode(succesResponse($results), true);

        return $this->respond($jsonRes, 200);
    }

    public function createTransfer()
    {
        $TransfersModel = new TransfersModel();

        // Validate token and get the request body
        try {
            $requestData = validateTokenAndFetchData();
        } catch (\Exception $e) {
            return $this->failUnauthorized($e->getMessage());
        }

        // Validate the JSON payload
        $expectedKeys = ["old_warehouse", "new_warehouse","old_warehouse_name","new_warehouse_name"];
        $requestDataKeys = array_keys($requestData);

        if (
            count($requestData) !== 4 ||
            !empty(array_diff($expectedKeys, $requestDataKeys))
        ) {
            return $this->fail("Invalid JSON Payload, check APIDocs.", 400);
        }
        
            $insertdata = [
                "old_warehouse" => $requestData["old_warehouse"],
                "new_warehouse" => $requestData["new_warehouse"],
                "old_warehouse_name" => $requestData["old_warehouse_name"],
                "new_warehouse_name" => $requestData["new_warehouse_name"]
            ];


        $insertedId = $TransfersModel->createTransfer($insertdata);

        
        $jsonRes = json_encode(succesResponse(['id_transfer' => $insertedId]), true);

        return $this->respond($jsonRes, 200);
    }

    public function transfersList()
    {
        $TransfersModel = new TransfersModel();

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

        
        $jsonRes = json_encode(succesResponse($TransfersModel->getTransferList($requestData["limit"], $requestData["offset"])), true);

        return $this->respond($jsonRes, 200);
    }

    public function searchProduct()
    {
        $TransfersModel = new TransfersModel();

        // Validate token and get the request body
        try {
            $requestData = validateTokenAndFetchData();
        } catch (\Exception $e) {
            return $this->failUnauthorized($e->getMessage());
        }

        // Validate the JSON payload
        $expectedKeys = ["searchterm", "old_warehouse_id"];
        $requestDataKeys = array_keys($requestData);

        if (
            count($requestData) !== 2 ||
            !empty(array_diff($expectedKeys, $requestDataKeys))
        ) {
            return $this->fail("Invalid JSON Payload, check APIDocs.", 400);
        }

        $results = $TransfersModel->searchProducts(
            $requestData["searchterm"],
            $requestData['old_warehouse_id']
        );

        $jsonRes = json_encode(succesResponse($results), true);

        return $this->respond($jsonRes, 200);
    }

    public function addProduct()
    {
    $TransfersModel = new TransfersModel();

    // Validate token and get the request body
    try {
        $requestData = validateTokenAndFetchData();
    } catch (\Exception $e) {
        return $this->failUnauthorized($e->getMessage());
    }

    $responses = $TransfersModel->insertProducts($requestData);


    $jsonRes = json_encode(succesResponse($requestData), true);

    return $this->respond($jsonRes, 200);
    }

    public function getTransfer()
    {
        $TransfersModel = new TransfersModel();

        // Validate token and get the request body
        try {
            $requestData = validateTokenAndFetchData();
        } catch (\Exception $e) {
            return $this->failUnauthorized($e->getMessage());
        }

        // Validate the JSON payload
        $expectedKeys = ["transfer_id"];
        $requestDataKeys = array_keys($requestData);

        if (
            count($requestData) !== 1 || !empty(array_diff($expectedKeys, $requestDataKeys))
        ) {
            return $this->fail("Invalid JSON Payload, check APIDocs.", 400);
        }
        if (!is_int($requestData["transfer_id"])) {
            return $this->fail("Invalid JSON Payload, check APIDocs.", 400);
        }

        
        
        $results = $TransfersModel->getTransferInfo($requestData["transfer_id"]);

        if ($results) {
            $jsonRes = json_encode(succesResponse($results), true);
            return $this->respond($jsonRes, 200);
        } else {
            return $this->failNotFound('No Transfer found with ID: '.$requestData["id"]);
        }

    }

    public function addToTransfer()
    {
    $TransferProductModel = new TransferProductModel();

    // Validate token and get the request body
    try {
        $requestData = validateTokenAndFetchData();
    } catch (\Exception $e) {
        return $this->failUnauthorized($e->getMessage());
    }

    $responses = $TransferProductModel->blockQuantityTransferProducts($requestData['transfer_id']);

    $jsonRes = json_encode(succesResponse($responses), true);

    return $this->respond($jsonRes, 200);
    }

    public function getTransferProductsList()
    {
        $TransferProductModel = new TransferProductModel();

        // Validate token and get the request body
        try {
            $requestData = validateTokenAndFetchData();
        } catch (\Exception $e) {
            return $this->failUnauthorized($e->getMessage());
        }
        if (!isset($requestData['transfer_id'])) {
            return $this->failBadRequest('Missing parameter.');
        }
        
        $jsonRes = json_encode(succesResponse($TransferProductModel->transfersProductsList($requestData['transfer_id'], $requestData)), true);

        return $this->respond($jsonRes, 200);
    }

    public function addProductTransfer() {
        $TransferProductModel = new TransferProductModel();
    
        // Validate token and get the request body
        try {
            $requestData = validateTokenAndFetchData();
        } catch (\Exception $e) {
            return $this->failUnauthorized($e->getMessage());
        }


        $eanCode = $requestData['ean_code'];
        $totalExecutions = $requestData['total_count_to_add_in_stock'];


        $responses = [];

        $counter = 0;

        while ($counter < $totalExecutions) {
            $responses[] = $TransferProductModel->processProduct($eanCode,$requestData['transfer_id']);
    
            $counter++;
        }
    
        return $this->respond(['responses' => $responses], 200); 

    }
    



}