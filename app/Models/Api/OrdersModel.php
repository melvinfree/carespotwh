<?php

namespace App\Models\Api;

use CodeIgniter\Model;

class OrdersModel extends Model
{
    protected $table = 'ci_bl_orders';
    protected $primaryKey = 'id';


    // Returning order list (including total with vat and without)
    // Used in Orders Controller for Endpoint "getAll"
    public function getOrdersList()
    {
        $this->select('id, invoice_company, delivery_price, tax_rate');
        $orders = $this->findAll();

        $orderProductsModel = new \App\Models\Api\OrderProductsModel();

        foreach ($orders as &$order) {
            $totalProductPricesVAT = $orderProductsModel->getTotalWithoutVat($order['id']);
            $totalProductPricesNOVAT = $orderProductsModel->getTotalWithVat($order['id']);

            $order['totalNoVat'] = $this->calculateShippingWithoutVat($order['delivery_price'], $order['tax_rate']) + $totalProductPricesVAT;
            $order['totalWithVat'] = $order['delivery_price'] + $totalProductPricesNOVAT;
        }

        return $orders;
    }

    private function calculateShippingWithoutVat($price, $vatRate)
    {
        return $price / (1 + $vatRate/100);
    }
}