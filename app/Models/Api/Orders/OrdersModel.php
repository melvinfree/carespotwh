<?php

namespace App\Models\Api\Orders;

use CodeIgniter\Model;

class OrdersModel extends Model
{
    protected $table = 'ci_bl_orders';
    protected $primaryKey = 'id';

    protected $allowedFields = ["order_notes", "whStatus"];


    // Returning order list (including total with vat and without)
    // Used in Orders Controller for Endpoint "getAll"
    public function getOrdersList($limit,$offset)
    {
        $this->select('ci_bl_orders.id, ci_customer_invoice_data.invoice_company, ci_bl_orders.delivery_price, ci_bl_orders.tax_rate, ci_bl_orders.status, ci_bl_orders.whStatus, ci_bl_orders.order_notes');
        $this->join('ci_customer_invoice_data', 'ci_bl_orders.customer_id = ci_customer_invoice_data.customer_id', 'left');
        $this->orderBy('ci_bl_orders.id', 'DESC');
        $this->limit($limit, $offset);
        $query = $this->get();
        $orders = $query->getResultArray();

        $orderProductsModel = new \App\Models\Api\Orders\OrderProductsModel();

        foreach ($orders as &$order) {
            $totalProductPricesVAT = $orderProductsModel->getTotalWithoutVat($order['id']);
            $totalProductPricesNOVAT = $orderProductsModel->getTotalWithVat($order['id']);

            $order['totalNoVat'] = round($this->calculateShippingWithoutVat($order['delivery_price'], $order['tax_rate']) + $totalProductPricesVAT, 2);
            $order['totalWithVat'] = round($order['delivery_price'] + $totalProductPricesNOVAT, 2);
        }

        return $orders;
    }


    
    // Returning order list (including total with vat and without)
    // Used in Orders Controller for Endpoint "searchOrder"
    public function searchOrderList($searchTerm, $limit, $offset)
    {
        $this->select('id, invoice_company, delivery_price, tax_rate, status, whStatus, order_notes');
        $this->like('invoice_company', $searchTerm)
         ->orLike('id', $searchTerm)
         ->orLike('email', $searchTerm)
         ->orLike('phone', $searchTerm);
        $this->orderBy('id', 'DESC');
        $this->limit($limit, $offset);
        $query = $this->get(); 
        $orders = $query->getResultArray(); 

        $orderProductsModel = new \App\Models\Api\Orders\OrderProductsModel();

        foreach ($orders as &$order) {
            $totalProductPricesVAT = $orderProductsModel->getTotalWithoutVat($order['id']);
            $totalProductPricesNOVAT = $orderProductsModel->getTotalWithVat($order['id']);

            $order['totalNoVat'] = round($this->calculateShippingWithoutVat($order['delivery_price'], $order['tax_rate']) + $totalProductPricesVAT, 2);
            $order['totalWithVat'] = round($order['delivery_price'] + $totalProductPricesNOVAT, 2);
        }

        return $orders;
    }

    // Returning order list filtered by status (including total with vat and without)
    // Used in Orders Controller for Endpoint "filterOrder"
    public function filterOrderList($status, $limit, $offset)
    {
        $this->select('id, invoice_company, delivery_price, tax_rate, status, whStatus, order_notes');
        $this->like('status', $status);
        $this->orderBy('id', 'DESC');
        $this->limit($limit, $offset);
        $query = $this->get(); 
        $orders = $query->getResultArray(); 

        $orderProductsModel = new \App\Models\Api\Orders\OrderProductsModel();

        foreach ($orders as &$order) {
            $totalProductPricesVAT = $orderProductsModel->getTotalWithoutVat($order['id']);
            $totalProductPricesNOVAT = $orderProductsModel->getTotalWithVat($order['id']);

            $order['totalNoVat'] = round($this->calculateShippingWithoutVat($order['delivery_price'], $order['tax_rate']) + $totalProductPricesVAT, 4);
            $order['totalWithVat'] = round($order['delivery_price'] + $totalProductPricesNOVAT, 4);
        }

        return $orders;
    }


    public function getOrderWithProducts($orderId)
    {
        $order = $this->select('*')
                    ->where('id', $orderId)
                    ->first();

        if ($order) {

            $orderProductsModel = new OrderProductsModel();
            $CInvoiceData = new \App\Models\Api\Customers\CInvoiceDataModel();
            $CDeliveryData = new \App\Models\Api\Customers\CDeliveryDataModel();

            $totalProductPricesVAT = $orderProductsModel->getTotalWithoutVat($order['id']);
            $totalProductPricesNOVAT = $orderProductsModel->getTotalWithVat($order['id']);

            $order['invoice_data'] = $CInvoiceData->getInvoiceData($order['customer_id']);
            $order['delivery_data'] = $CDeliveryData->getDeliveryData($order['customer_id']);
            $order['totalNoVat'] = round($this->calculateShippingWithoutVat($order['delivery_price'], $order['tax_rate']) + $totalProductPricesVAT, 4);
            $order['totalWithVat'] = round($order['delivery_price'] + $totalProductPricesNOVAT, 4);
            $order['orderProducts'] = $orderProductsModel->getOrderProducts($orderId);
        }

        return $order;
    }

    // Export orders products in excel
    // Used in orders list

    public function getOListExcelFormat($orderId)
    {
        // Get currency from ci_bl_orders table
        $order = $this->select('currency')
            ->where('id', $orderId)
            ->first();

        if ($order) {
        $currency = $order['currency'];

        // Get product details from ci_bl_order_products table
        $orderProductsModel = new OrderProductsModel();
        $orderProducts = $orderProductsModel->getPListExcelFormat($orderId, ['name', 'sku', 'ean', 'quantity', 'price_brutto']);

        return [
            'currency' => $currency,
            'orderProducts' => $orderProducts
        ];
        }
    }

    // Delete order
    public function deleteOrder($id)
{
    // First, find the order by id
    $order = $this->find($id);
    
    // If the order doesn't exist, return an error message
    if ($order === null) {
        return [
            'error' => true,
            'message' => 'Order not found'
        ];
    }

    // If the order does exist, delete it and return a success message
    $this->delete(['id' => $id]);

    return [
        'error' => false,
        'message' => 'Order deleted'
    ];
}

    

    private function calculateShippingWithoutVat($price, $vatRate)
    {
        return $price / (1 + $vatRate/100);
    }


    private function setOrderNotes($requestData)
    {

        $order = $this->find($requestData["order_id"]);

        if ($order === null) {
            return [
                'error' => true,
                'message' => 'Order not found'
            ];
        }

        $this->set("order_notes", $requestData["order_notes"])
        ->where("id", $requestData["order_id"])
        ->update();
        
        return [
            'error' => false,
            'message' => 'Order notes succesfuly updated'
        ];

        
    }

    private function setOrderStatus($requestData)
    {

        $order = $this->find($requestData["order_id"]);

        if ($order === null) {
            return [
                'error' => true,
                'message' => 'Order not found'
            ];
        }

        $this->set("whStatus", $requestData["order_status"])
        ->where("id", $requestData["order_id"])
        ->update();
        
        return [
            'error' => false,
            'message' => 'Order notes succesfuly updated'
        ];

        
    }

    
}