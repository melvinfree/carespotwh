<?php

namespace App\Models\Api;

use CodeIgniter\Model;

class OrderProductsModel extends Model
{
    protected $table = 'ci_bl_order_products';
    protected $primaryKey = 'id';

    public function getTotalProductPrices($orderId)
    {
        $this->select('price_brutto, tax_rate');
        $this->where('order_id', $orderId);
        $products = $this->findAll();

        $total = 0;
        foreach ($products as $product) {
            $total += $this->calculatePriceWithoutVAT($product['price_brutto'], $product['tax_rate']);
        }

        return $total;
    }

    private function calculatePriceWithoutVAT($price, $vatRate)
    {
        return $price / (1 + $vatRate/100);
    }
}