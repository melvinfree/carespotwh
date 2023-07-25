<?php

namespace App\Controllers\Api;

use CodeIgniter\API\ResponseTrait;
use App\Models\Api\Inventory\ProductsModel;
use CodeIgniter\Controller;
use App\Models\Api\PurchaseOrder\PurchaseOrderModel;
use App\Models\Api\PurchaseOrder\PurchaseOrderProductModel;
use DateTime;

class Purchase extends Controller
{

    use ResponseTrait;

    public function __construct()
    {
        helper("api");
        helper("currency");
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
        $expectedKeys = ["supplier_id", "supplier_name", "invoice_series", "invoice_number", "invoice_date", "currency" , "warehouse_id", "warehouse_name"];
        $requestDataKeys = array_keys($requestData);

        if (
            count($requestData) !== 8 ||
            !empty(array_diff($expectedKeys, $requestDataKeys))
        ) {
            return $this->fail("Invalid JSON Payload, check APIDocs.", 400);
        }
        if (!is_string($requestData['currency']) || !is_string($requestData['supplier_name']) || !is_string($requestData['warehouse_name']) || !is_int($requestData["supplier_id"]) || !is_string($requestData['invoice_series']) || !is_string($requestData['currency']) || !is_int($requestData["invoice_number"]) || !is_int($requestData["warehouse_id"])) {
            return $this->fail("Invalid JSON Payload, check APIDocs.", 400);
        }

        if($requestData['currency'] == "RON"){
        
            $insertdata = [
                "supplier_id" => $requestData["supplier_id"],
                "supplier_name" => $requestData["supplier_name"],
                "invoice_series" => $requestData["invoice_series"],
                "number" => $requestData["invoice_number"],
                "invoice_date" => $requestData["invoice_date"],
                "transport" => 1, // Courier / just one option
                "currency" => $requestData["currency"],
                "warehouse_id" => $requestData['warehouse_id'],
                "warehouse_name" => $requestData['warehouse_name'],
                "currency_rate" => 1
            ];
        }

        elseif($requestData['currency'] != "RON"){
        
            $insertdata = [
                "supplier_id" => $requestData["supplier_id"],
                "supplier_name" => $requestData["supplier_name"],
                "invoice_series" => $requestData["invoice_series"],
                "number" => $requestData["invoice_number"],
                "invoice_date" => $requestData["invoice_date"],
                "transport" => 1, // Courier / just one option
                "currency" => $requestData["currency"],
                "warehouse_id" => $requestData['warehouse_id'],
                "warehouse_name" => $requestData['warehouse_name'],
                "currency_rate" => getCurrencyRate($requestData["currency"])
            ];
        }

        $insertedId = $PurchaseOrder->createPurchase($insertdata);

        
        $jsonRes = json_encode(succesResponse(['id_nir' => $insertedId]), true);

        return $this->respond($jsonRes, 200);
    }


    public function getProductsList()
    {
        $PurchaseOrder = new PurchaseOrderModel();
        $ProductsModel = new ProductsModel();

        // Validate token and get the request body
        try {
            $requestData = validateTokenAndFetchData();
        } catch (\Exception $e) {
            return $this->failUnauthorized($e->getMessage());
        }
        
        $jsonRes = json_encode(succesResponse($ProductsModel->getProductsList()), true);

        return $this->respond($jsonRes, 200);
    }

    // Retrive a list of specific orders based on searchterm (useful for orderlist)- , available terms [invoice_company/orderid/email/phone]
    // Payload type: JSON
    // Payload format:
    // {"searchterm": "string", "limit": int, "offset": int}
    // Response - to be added
    public function searchProduct()
    {
        $ProductsModel = new ProductsModel();

        // Validate token and get the request body
        try {
            $requestData = validateTokenAndFetchData();
        } catch (\Exception $e) {
            return $this->failUnauthorized($e->getMessage());
        }

        // Validate the JSON payload
        $expectedKeys = ["searchterm"];
        $requestDataKeys = array_keys($requestData);

        if (
            count($requestData) !== 1 ||
            !empty(array_diff($expectedKeys, $requestDataKeys))
        ) {
            return $this->fail("Invalid JSON Payload, check APIDocs.", 400);
        }

        $results = $ProductsModel->searchProducts(
            $requestData["searchterm"]
        );

        $jsonRes = json_encode(succesResponse($results), true);

        return $this->respond($jsonRes, 200);
    }

    public function addProduct()
    {
    $PurchaseOrderProductModel = new PurchaseOrderProductModel();

    // Validate token and get the request body
    try {
        $requestData = validateTokenAndFetchData();
    } catch (\Exception $e) {
        return $this->failUnauthorized($e->getMessage());
    }

    $responses = $PurchaseOrderProductModel->insertProducts($requestData);

    $jsonRes = json_encode(succesResponse($responses), true);

    return $this->respond($jsonRes, 200);
    }


    public function addToStock()
    {
    $PurchaseOrderProductModel = new PurchaseOrderProductModel();

    // Validate token and get the request body
    try {
        $requestData = validateTokenAndFetchData();
    } catch (\Exception $e) {
        return $this->failUnauthorized($e->getMessage());
    }

    $responses = $PurchaseOrderProductModel->addInvoiceProductsToStock($requestData['invoice_id']);

    $jsonRes = json_encode(succesResponse($responses), true);

    return $this->respond($jsonRes, 200);
    }


    public function changeProductInvoice()
    {
    $PurchaseOrderProductModel = new PurchaseOrderProductModel();

    // Validate token and get the request body
    try {
        $requestData = validateTokenAndFetchData();
    } catch (\Exception $e) {
        return $this->failUnauthorized($e->getMessage());
    }
    $responses = $PurchaseOrderProductModel->updateInvoiceId($requestData['current_invoice_id'], $requestData['new_invoice_id'], $requestData['row_id']);

    $jsonRes = json_encode(succesResponse($responses), true);

    return $this->respond($jsonRes, 200);
    }

    public function deleteProduct()
    {
    $PurchaseOrderProductModel = new PurchaseOrderProductModel();

    // Validate token and get the request body
    try {
        $requestData = validateTokenAndFetchData();
    } catch (\Exception $e) {
        return $this->failUnauthorized($e->getMessage());
    }
    $responses = $PurchaseOrderProductModel->deleteProduct($requestData['row_id'], $requestData['invoice_id']);

    $jsonRes = json_encode(succesResponse($responses), true);

    return $this->respond($jsonRes, 200);
    }

    public function setInvoiceValues()
    {
    $PurchaseOrderModel = new PurchaseOrderModel();

    // Validate token and get the request body
    try {
        $requestData = validateTokenAndFetchData();
    } catch (\Exception $e) {
        return $this->failUnauthorized($e->getMessage());
    }
    $responses = $PurchaseOrderModel->modifyInvoice($requestData);

    $jsonRes = json_encode(succesResponse($responses), true);

    return $this->respond($jsonRes, 200);
    }


    public function getInvoicePList()
    {
    $PurchaseOrderProductModel = new PurchaseOrderProductModel();

    // Validate token and get the request body
    try {
        $requestData = validateTokenAndFetchData();
    } catch (\Exception $e) {
        return $this->failUnauthorized($e->getMessage());
    }
    $responses = $PurchaseOrderProductModel->get_invoice_products($requestData['invoice_id']);

    $jsonRes = json_encode(succesResponse($responses), true);

    return $this->respond($jsonRes, 200);
    }


}