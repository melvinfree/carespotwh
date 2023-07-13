<?php

namespace App\Models\Api;

use CodeIgniter\Model;

class OrdersModel extends Model
{
    protected $table = 'ci_bl_orders';
    protected $primaryKey = 'id';

    public function getOrdersWithTotal()
    {
        $this->select('id, delivery_price, tax_rate');
        $orders = $this->findAll();

        $orderProductsModel = new \App\Models\Api\OrderProductsModel();

        foreach ($orders as &$order) {
            $totalProductPrices = $orderProductsModel->getTotalProductPrices($order['id']);
            $order['total'] = $this->calculatePriceWithoutVAT($order['delivery_price'], $order['tax_rate']) + $totalProductPrices;
        }

        return $orders;
    }

    private function calculatePriceWithoutVAT($price, $vatRate)
    {
        return $price / (1 + $vatRate/100);
    }
}