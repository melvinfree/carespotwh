<?php

namespace App\Controllers\Api;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;
use App\Models\Api\PurchaseOrder\PurchaseOrderModel;

class Purchase extends Controller
{

    use ResponseTrait;

    public function __construct()
    {
        helper("api");
    }

    public function purchaseInit()
    {
        $PurchaseOrder = new PurchaseOrderModel();

        // Validate token and get the request body
        try {
            $requestData = validateTokenAndFetchData();
        } catch (\Exception $e) {
            return $this->failUnauthorized($e->getMessage());
        }

        $results = $PurchaseOrder->getInfo();

        $jsonRes = json_encode(succesResponse($results), true);

        return $this->respond($jsonRes, 200);
    }

    public function purchaseList()
    {
        $PurchaseOrder = new PurchaseOrderModel();

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

        
        $jsonRes = json_encode(succesResponse($PurchaseOrder->getPurchaseList($requestData["limit"], $requestData["offset"])), true);

        return $this->respond($jsonRes, 200);
    }


}