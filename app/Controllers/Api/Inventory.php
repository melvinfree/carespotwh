<?php

namespace App\Controllers\Api;

use CodeIgniter\API\ResponseTrait;
use App\Models\Api\Inventory\ProductsModel;
use CodeIgniter\Controller;
use App\Models\Api\PurchaseOrder\PurchaseOrderModel;
use App\Models\Api\PurchaseOrder\PurchaseOrderProductModel;
use App\Models\Api\Inventory\ReceptionsModel;
use App\Models\Api\Inventory\TransfersModel;
use App\Models\Api\Inventory\StockModel;
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
        if (!isset($requestData['invoice_id'])) {
            return $this->failBadRequest('Missing parameter.');
        }
        
        $jsonRes = json_encode(succesResponse($Receptions->receptionsProductsList($requestData['invoice_id'])), true);

        return $this->respond($jsonRes, 200);
    }

    public function getNotConfirmedProductPcs()
    {
        $Receptions = new ReceptionsModel();

        // Validate token and get the request body
        try {
            $requestData = validateTokenAndFetchData();
        } catch (\Exception $e) {
            return $this->failUnauthorized($e->getMessage());
        }

        
        $jsonRes = json_encode(succesResponse($Receptions->notConfirmedProductPcs($requestData['invoice_product_row_id'])), true);

        return $this->respond($jsonRes, 200);
    }

    public function getConfirmedProductPcs()
    {
        $Receptions = new ReceptionsModel();

        // Validate token and get the request body
        try {
            $requestData = validateTokenAndFetchData();
        } catch (\Exception $e) {
            return $this->failUnauthorized($e->getMessage());
        }

        
        $jsonRes = json_encode(succesResponse($Receptions->ConfirmedProductPcs($requestData['invoice_product_row_id'])), true);

        return $this->respond($jsonRes, 200);
    }


    public function addProductInventory() {
        $Receptions = new ReceptionsModel();
    
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
            $responses[] = $Receptions->processProduct($eanCode,$requestData['invoice_in_id']);
    
            $counter++;
        }
    
        return $this->respond(['responses' => $responses], 200); 

    }

    public function ProductsStock()
    {

        
        // Load the StockModel
        $stockModel = new StockModel();

        // Validate token and get the request body
        try {
            $requestData = validateTokenAndFetchData();
        } catch (\Exception $e) {
            return $this->failUnauthorized($e->getMessage());
        }

        // Fetch all distinct product codes from the stock table
        $distinctProductCodes = $stockModel->distinct('product_id')->findColumn('product_id');

        // Initialize an array to store the product codes and their stock counts
        $productStock = [];

        foreach ($distinctProductCodes as $productCode) {
            // Get the stock count for the current product code
            $stockCount = $stockModel->getProductStockCount($productCode);

            // Store the product code and stock count in the array
            $productStock[] = [
                'product_id' => $productCode,
                'stock' => $stockCount
            ];
        }

        // Convert the array to JSON
        $jsonData = json_encode($productStock, JSON_UNESCAPED_SLASHES);

        return $this->respond($jsonData, 200);


    }
    
    


}