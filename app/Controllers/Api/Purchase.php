<?php

namespace App\Controllers\Api;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;
use App\Models\Api\PurchaseModels\PurchaseOrder;

class Purchase extends Controller
{

    use ResponseTrait;

    public function __construct()
    {
        helper("api");
    }

    public function purchaseInit()
    {
        $PurchaseOrder = new PurchaseOrder();

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
        $PurchaseOrder = new PurchaseOrder();

        // Validate token and get the request body
        try {
            $requestData = validateTokenAndFetchData();
        } catch (\Exception $e) {
            return $this->failUnauthorized($e->getMessage());
        }

        
        $jsonRes = json_encode(succesResponse($PurchaseOrder->getPurchaseList()), true);

        return $this->respond($jsonRes, 200);
    }


}