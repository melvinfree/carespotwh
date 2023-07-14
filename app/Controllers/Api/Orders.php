<?php

namespace App\Controllers\Api;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;
use App\Models\Api\OrdersModel;


class Orders extends Controller
{
    use ResponseTrait;

    public function __construct() {
        helper('api');
    }


    // Retrive a list of order based on limit & offset
    // Payload type: JSON
    // Endpoint: https://whx.ybomedia.ro/Api/Orders/getAll
    // Payload format: 
    // {"offset": int, "limit": int} 
    // Response - to be added
    public function getAll(){

        $OrdersModel = new OrdersModel();

		
	// Validate token and get the request body
    try {
        $requestData = validateTokenAndFetchData();
    } catch (\Exception $e) {
        return $this->failUnauthorized($e->getMessage());
    }

    // Validate the JSON payload
    $expectedKeys = ['limit', 'offset'];
    $requestDataKeys = array_keys($requestData);

    if (count($requestData) !== 2 || !empty(array_diff($expectedKeys, $requestDataKeys))) {
        return $this->fail('Invalid JSON Payload, check APIDocs.', 400);
    }
    if (!is_int($requestData['limit']) || !is_int($requestData['limit'])) {
        return $this->fail('Invalid JSON Payload, check APIDocs.', 400);
    }


    // Process the valid JSON payload
    // ...
    
    $results = $OrdersModel->getOrdersList($requestData['limit'], $requestData['offset']);

    $jsonRes = json_encode(succesResponse($results),true);

    return $this->respond($jsonRes, 200);
   }


   
    // Retrive a list of specific orders based on searchterm (useful for orderlist)- , available terms [invoice_company/orderid/email/phone]
    // Payload type: JSON
    // Payload format: 
    // {"searchterm": "string", "limit": int, "offset": int}
    // Response - to be added
   public function searchOrder(){

    $OrdersModel = new OrdersModel();
		
	// Validate token and get the request body
    try {
        $requestData = validateTokenAndFetchData();
    } catch (\Exception $e) {
        return $this->failUnauthorized($e->getMessage());
    }

    // Validate the JSON payload
    $expectedKeys = ['searchterm','limit','offset'];
    $requestDataKeys = array_keys($requestData);

    if (count($requestData) !== 3 || !empty(array_diff($expectedKeys, $requestDataKeys))) {
        return $this->fail('Invalid JSON Payload, check APIDocs.', 400);
    }


    $results = $OrdersModel->searchOrderList($requestData['searchterm'], $requestData['limit'], $requestData['offset']);

    $jsonRes = json_encode(succesResponse($results),true);

    return $this->respond($jsonRes, 200);
    
   }


    // Retrive a list of specific orders based on order STATUS (useful for orderlist)
    // Payload type: JSON
    // Payload format: 
    // {"status": "string", "limit": int, "offset": int}
    // Response - to be added
    public function filterOrder(){

        $OrdersModel = new OrdersModel();
            
        // Validate token and get the request body
        try {
            $requestData = validateTokenAndFetchData();
        } catch (\Exception $e) {
            return $this->failUnauthorized($e->getMessage());
        }
    
        // Validate the JSON payload
        $expectedKeys = ['status','limit','offset'];
        $requestDataKeys = array_keys($requestData);
    
        if (count($requestData) !== 3 || !empty(array_diff($expectedKeys, $requestDataKeys))) {
            return $this->fail('Invalid JSON Payload, check APIDocs.', 400);
        }
    
    
        $results = $OrdersModel->filterOrderList($requestData['status'], $requestData['limit'], $requestData['offset']);
    
        $jsonRes = json_encode(succesResponse($results),true);
    
        return $this->respond($jsonRes, 200);
        
       }

    // Delete order based on id
    // Payload type: JSON
    // Payload format: 
    // {"id": int}
    // Good Response {"error":false,"message":"Order deleted"}
    // Bad Response {"error":true,"message":"Order not found"}

    public function deleteOrder()
    {
        $OrdersModel = new OrdersModel();
        
        // Validate token and get the request body
        try {
            $requestData = validateTokenAndFetchData();
        } catch (\Exception $e) {
            return $this->failUnauthorized($e->getMessage());
        }

        if (!isset($requestData['id'])) {
            return $this->failValidationError('Missing mandatory field: id');
        }

        $id = $requestData['id'];

        // Delete the order from the database
        $results = $OrdersModel->deleteOrder($requestData['id']);

        $jsonRes = json_encode($results,true);

        // Return a success message
        return $this->respondDeleted($jsonRes);
    }


    public function getPlist()
    {
        $OrdersModel = new OrdersModel();
        
        // Validate token and get the request body
        try {
            $requestData = validateTokenAndFetchData();
        } catch (\Exception $e) {
            return $this->failUnauthorized($e->getMessage());
        }

        
        if (!isset($requestData['id'])) {
            return $this->failValidationError('Missing mandatory field: id');
        }

        $id = $requestData['id'];

        // Delete the order from the database
        $results = $OrdersModel->getOListExcelFormat($requestData['id']);

        $jsonRes = json_encode(succesResponse($results),true);

        // Return a success message
        return $this->respond($jsonRes, 200);
    }






    // Retrive a specific order based on ID
    // Payload type: JSON
    // Payload format: 
    // {"orderid": int}
    // Response - to be added
   public function getOrder(){
    // Token authorization & Expected payload & getJSON(get payload)
    $token = $this->request->getHeaderLine('YBO-Token');
    if ($token !== getenv('PRIVATE_TOKEN')) {
        return $this->failUnauthorized('Invalid token');
    }

    $requestData = $this->request->getJSON(true);
    $expectedKeys = ['orderid'];
    $requestDataKeys = array_keys($requestData);

    if (count($requestData) !== 1 || !empty(array_diff($expectedKeys, $requestDataKeys))) {
		
        return $this->fail('Invalid JSON Payload, check APIDocs.', 400);
    }

    if (!is_int($requestData['orderid'])) {
        return $this->fail('Invalid orderid. It should be an integer.', 400);
    }

    // Send the response if all it's ok

    // Process the valid JSON payload
    // ...

    return $this->respond($requestData, 200);

   }


}