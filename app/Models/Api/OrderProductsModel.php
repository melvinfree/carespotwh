<?php

namespace App\Models\Api;

use CodeIgniter\Model;

class OrderProductsModel extends Model
{
    protected $table = 'ci_bl_order_products';
    protected $primaryKey = 'id';


    // [ORDER PRODUCTS TOTAL WITHOUT VAT] 
    // Calculate product prices (all product prices from a specific order without VAT (net price))

    public function getTotalWithoutVat($orderId)
    {
        $this->select('price_brutto, tax_rate, quantity');
        $this->where('order_id', $orderId);
        $products = $this->findAll();

        $total = 0;
        foreach ($products as $product) {
            $total += $this->calculatePriceWithoutVAT($product['price_brutto'], $product['tax_rate'], $product['quantity']);
        }

        return $total;
    }

    private function calculatePriceWithoutVAT($price, $vatRate, $quantity)
    {
        return ($price / (1 + $vatRate/100)) * $quantity;
    }



    // [ORDER PRODUCTS TOTAL WITH VAT] 
    // Calculate product prices (all product prices from a specific order including VAT (brut price))

    public function getTotalWithVat($orderId)
    {
        $this->select('price_brutto, quantity');
        $this->where('order_id', $orderId);
        $products = $this->findAll();

        $total = 0;
        foreach ($products as $product) {
            $total += $this->calculatePriceWithVAT($product['price_brutto'], $product['quantity']);
        }

        return $total;
    }


    private function calculatePriceWithVAT($price, $quantity)
    {
        return $price * $quantity;
    }

    
}