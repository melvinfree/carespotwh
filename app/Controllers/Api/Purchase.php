<?php

namespace App\Controllers\Api;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;
use App\Models\Api\PurchaseOrder\PurchaseOrderModel;
use DateTime;

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

    public function createPurchase()
    {
        $PurchaseOrder = new PurchaseOrderModel();

        // Validate token and get the request body
        try {
            $requestData = validateTokenAndFetchData();
        } catch (\Exception $e) {
            return $this->failUnauthorized($e->getMessage());
        }

        // Validate the JSON payload
        $expectedKeys = ["supplier_id", "warehouse_id", "invoice_series", "invoice_number", "invoice_date"];
        $requestDataKeys = array_keys($requestData);

        $date = DateTime::createFromFormat('Y-m-d', $requestData['invoice_date']);

        if (
            count($requestData) !== 6 ||
            !empty(array_diff($expectedKeys, $requestDataKeys))
        ) {
            return $this->fail("Invalid JSON Payload, check APIDocs.", 400);
        }
        if (!($date && $date->format('Y-m-d') == $requestData['invoice_date']) || !is_int($requestData["supplier_id"]) || !is_string($requestData['invoice_series']) || !is_string($requestData['currency']) || !is_int($requestData["invoice_number"])) {
            return $this->fail("Invalid JSON Payload, check APIDocs.", 400);
        }

        $insertdata = [
            "supplier_id" => $requestData["supplier_id"],
            "invoice_series" => $requestData["invoice_series"],
            "invoice_number" => $requestData["invoice_number"],
            "invoice_date" => $requestData["invoice_date"],
            "transport" => 1, // Courier / just one option
            "currency" => $requestData["invoice_date"]
        ];

        $insertedId = $PurchaseOrder->insertData($insertdata);

        
        $jsonRes = json_encode(succesResponse(['id_nir' => $insertedId]), true);

        return $this->respond($jsonRes, 200);
    }


}