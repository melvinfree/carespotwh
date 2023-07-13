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

    // Delete order
    public function deleteOrder($id)
    {
        return $this->delete(['id' => $id]);
        
    }

    

    private function calculateShippingWithoutVat($price, $vatRate)
    {
        return $price / (1 + $vatRate/100);
    }
}