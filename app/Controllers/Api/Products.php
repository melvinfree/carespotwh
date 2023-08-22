<?php

namespace App\Controllers\Api;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;
use App\Models\Api\Products\ProductsModel;

class Products extends Controller
{

    use ResponseTrait;

    public function __construct()
    {
        helper("api");
        helper("currency");
    }

    public function getProductsList(){

        $ProductsModel = new ProductsModel();

        // Validate token and get the request body
        try {
            $requestData = validateTokenAndFetchData();
        } catch (\Exception $e) {
            return $this->failUnauthorized($e->getMessage());
        }

        if (!isset($requestData['limit']) || !isset($requestData['offset'])) {
            return $this->failBadRequest('Missing limit and/or offset in request data.');
        }

        $results = $ProductsModel->getProductsListModel($requestData['limit'], $requestData['offset']);

        $jsonRes = json_encode(succesResponse($results), true);

        return $this->respond($jsonRes, 200);

    }


}