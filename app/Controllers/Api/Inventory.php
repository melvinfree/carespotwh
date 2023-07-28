<?php

namespace App\Controllers\Api;

use CodeIgniter\API\ResponseTrait;
use App\Models\Api\Inventory\ProductsModel;
use CodeIgniter\Controller;
use App\Models\Api\PurchaseOrder\PurchaseOrderModel;
use App\Models\Api\PurchaseOrder\PurchaseOrderProductModel;
use App\Models\Api\Inventory\InventoryModel;
use DateTime;

class Inventory extends Controller
{
    use ResponseTrait;

    public function __construct()
    {
        helper("api");
        helper("currency");
    }

    public function getReceptionsList(){

        $InventoryModel = new InventoryModel();

        // Validate token and get the request body
        try {
            $requestData = validateTokenAndFetchData();
        } catch (\Exception $e) {
            return $this->failUnauthorized($e->getMessage());
        }

        $results = $InventoryModel->receptionsList($requestData['limit'], $requestData['offset']);

        $jsonRes = json_encode(succesResponse($results), true);

        return $this->respond($jsonRes, 200);

    }

    


}