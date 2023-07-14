<?php

namespace App\Models\Api;

use CodeIgniter\Model;

class OrdersModel extends Model
{
    protected $table = 'ci_bl_orders';
    protected $primaryKey = 'id';


    // Returning order list (including total with vat and without)
    // Used in Orders Controller for Endpoint "getAll"
    public function getOrdersList($limit,$offset)
    {
        $this->select('id, invoice_company, delivery_price, tax_rate, status, whStatus, order_notes');
        $this->orderBy('id', 'DESC');
        $this->limit($limit, $offset);
        $query = $this->get(); 
        $orders = $query->getResultArray(); 

        $orderProductsModel = new \App\Models\Api\OrderProductsModel();

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

        $orderProductsModel = new \App\Models\Api\OrderProductsModel();

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

        $orderProductsModel = new \App\Models\Api\OrderProductsModel();

        foreach ($orders as &$order) {
            $totalProductPricesVAT = $orderProductsModel->getTotalWithoutVat($order['id']);
            $totalProductPricesNOVAT = $orderProductsModel->getTotalWithVat($order['id']);

            $order['totalNoVat'] = round($this->calculateShippingWithoutVat($order['delivery_price'], $order['tax_rate']) + $totalProductPricesVAT, 2);
            $order['totalWithVat'] = round($order['delivery_price'] + $totalProductPricesNOVAT, 2);
        }

        return $orders;
    }

    // Export orders products in excel
    // Used in orders list

    public function getOListExcelFormat($orderId)
    {
        // Get currency from ci_bl_orders table
        $currency = $this->select('currency')
            ->where('id', $orderId)
            ->first()
            ->currency;

        // Get product details from ci_bl_order_products table
        $orderProductsModel = new OrderProductsModel();
        $orderProducts = $orderProductsModel->getByOrderId($orderId, ['name', 'sku', 'ean', 'quantity', 'price_brutto']);

        return [
            'currency' => $currency,
            'orderProducts' => $orderProducts
        ];
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
}