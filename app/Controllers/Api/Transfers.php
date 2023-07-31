<?php

namespace App\Controllers\Api;

use CodeIgniter\API\ResponseTrait;
use App\Models\Api\Inventory\ProductsModel;
use CodeIgniter\Controller;
use App\Models\Api\PurchaseOrder\PurchaseOrderModel;
use App\Models\Api\PurchaseOrder\PurchaseOrderProductModel;
use App\Models\Api\Inventory\ReceptionsModel;
use App\Models\Api\Inventory\TransfersModel;
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
                "new_warehouse" => $requestData["old_warehouse"],
                "old_warehouse_name" => $requestData["old_warehouse_name"],
                "new_warehouse_name" => $requestData["new_warehouse_name"]
            ];


        $insertedId = $TransfersModel->createTransfer($insertdata);

        
        $jsonRes = json_encode(succesResponse(['id_transfer' => $insertedId]), true);

        return $this->respond($jsonRes, 200);
    }



}