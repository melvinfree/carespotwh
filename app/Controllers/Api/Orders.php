<?php

namespace App\Controllers\Api;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;

class Orders extends Controller
{
    use ResponseTrait;

    public function getAll(){
		
	$token = $this->request->getHeaderLine('YBO-Token');
    if ($token !== 'Token123123') {
        return $this->failUnauthorized('Invalid token');
    }
		
    $requestData = $this->request->getJSON(true); // Retrieve JSON payload as an associative array

    // Validate the JSON payload
    $expectedKeys = ['limit', 'offset'];
    $requestDataKeys = array_keys($requestData);

    if (count($requestData) !== 2 || !empty(array_diff($expectedKeys, $requestDataKeys))) {
        // Invalid JSON payload, return an error response
		
        return $this->fail('Invalid JSON payload', 400);
    }

    // Process the valid JSON payload
    // ...

    return $this->respond($requestData, 200);
   }

   public function getOrder(){
    // Token authorization & Expected payload & getJSON(get payload)
    $token = $this->request->getHeaderLine('YBO-Token');
    if ($token !== 'Token123123') {
        return $this->failUnauthorized('Invalid token');
    }

    $requestData = $this->request->getJSON(true);
    $expectedKeys = ['orderid'];
    $requestDataKeys = array_keys($requestData);

    if (count($requestData) !== 1 || !empty(array_diff($expectedKeys, $requestDataKeys))) {
		
        return $this->fail('Payload-ul este invalid, format corect: {"orderid": IDComanda}', 400);
    }

    // END Token authorization & Expected payload & getJSON(get payload)

    // Send the response if all it's ok

    // Process the valid JSON payload
    // ...

    return $this->respond($requestData, 200);

   }
}