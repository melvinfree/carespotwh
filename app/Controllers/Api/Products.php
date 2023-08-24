<?php

namespace App\Controllers\Api;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;
use App\Models\Api\Products\ProductsModel;
use App\Models\Api\Products\ProductsEansModel;

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


    public function getProduct()
    {
        $ProductsModel = new ProductsModel();

        // Validate token and get the request body
        try {
            $requestData = validateTokenAndFetchData();
        } catch (\Exception $e) {
            return $this->failUnauthorized($e->getMessage());
        }

        // Validate the JSON payload
        $expectedKeys = ["product_id"];
        $requestDataKeys = array_keys($requestData);

        if (
            count($requestData) !== 1 || !empty(array_diff($expectedKeys, $requestDataKeys))
        ) {
            return $this->fail("Invalid JSON Payload, check APIDocs.", 400);
        }
        if (!is_int($requestData["product_id"])) {
            return $this->fail("Invalid JSON Payload, check APIDocs.", 400);
        }

        
        
        $results = $ProductsModel->getProductDetails($requestData["product_id"]);

        if ($results) {
            $jsonRes = json_encode(succesResponse($results), true);
            return $this->respond($jsonRes, 200);
        } else {
            return $this->failNotFound('No product found with ID: '.$requestData["product_id"]);
        }

    }



    public function deleteEan()
    {
        $ProductsEansModel = new ProductsEansModel();

        // Validate token and get the request body
        try {
            $requestData = validateTokenAndFetchData();
        } catch (\Exception $e) {
            return $this->failUnauthorized($e->getMessage());
        }

        // Validate the JSON payload
        $expectedKeys = ["row_id"];
        $requestDataKeys = array_keys($requestData);

        if (
            count($requestData) !== 1 || !empty(array_diff($expectedKeys, $requestDataKeys))
        ) {
            return $this->fail("Invalid JSON Payload, check APIDocs.", 400);
        }
        if (!is_int($requestData["product_id"])) {
            return $this->fail("Invalid JSON Payload, check APIDocs.", 400);
        }

        
        
        $results = $ProductsEansModel->deleteEan($requestData["row_id"]);

        if ($results) {
            $jsonRes = json_encode(succesResponse($results), true);
            return $this->respond($jsonRes, 200);
        } else {
            return $this->failNotFound('No ean found with ROW ID: '.$requestData["row_id"]);
        }

    }

    public function addEan()
    {
        $ProductsEansModel = new ProductsEansModel();

        // Validate token and get the request body
        try {
            $requestData = validateTokenAndFetchData();
        } catch (\Exception $e) {
            return $this->failUnauthorized($e->getMessage());
        }

        // Validate the JSON payload
        $expectedKeys = ["product_id", "ean"];
        $requestDataKeys = array_keys($requestData);

        $insertedId = $ProductsEansModel->addEan($requestData['product_id'],$requestData['ean']);

        
        $jsonRes = json_encode(succesResponse(['id_ean_adaugat' => $insertedId]), true);

        return $this->respond($jsonRes, 200);
    }


}