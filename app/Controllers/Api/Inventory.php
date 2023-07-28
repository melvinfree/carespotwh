<?php

namespace App\Controllers\Api;

use CodeIgniter\API\ResponseTrait;
use App\Models\Api\Inventory\ProductsModel;
use CodeIgniter\Controller;
use App\Models\Api\PurchaseOrder\PurchaseOrderModel;
use App\Models\Api\PurchaseOrder\PurchaseOrderProductModel;
use App\Models\Api\Inventory\ReceptionsModel;
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

        $Receptions = new ReceptionsModel();

        // Validate token and get the request body
        try {
            $requestData = validateTokenAndFetchData();
        } catch (\Exception $e) {
            return $this->failUnauthorized($e->getMessage());
        }

        if (!isset($requestData['limit']) || !isset($requestData['offset'])) {
            return $this->failBadRequest('Missing limit and/or offset in request data.');
        }

        $results = $Receptions->receptionsList($requestData['limit'], $requestData['offset']);

        $jsonRes = json_encode(succesResponse($results), true);

        return $this->respond($jsonRes, 200);

    }

    public function getProductsList()
    {
        $Receptions = new ReceptionsModel();

        // Validate token and get the request body
        try {
            $requestData = validateTokenAndFetchData();
        } catch (\Exception $e) {
            return $this->failUnauthorized($e->getMessage());
        }
        
        $jsonRes = json_encode(succesResponse($Receptions->receptionsProductsList()), true);

        return $this->respond($jsonRes, 200);
    }

    


}