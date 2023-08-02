<?php

namespace App\Models\Api\Orders;
use App\Models\Api\Inventory\StockModel;

use CodeIgniter\Model;

class OrderProductsModel extends Model
{
    protected $table = 'ci_bl_order_products';
    protected $primaryKey = 'id';
    protected $allowedFields = ["quantity", "price_brutto"];


    
    public function getPListExcelFormat($orderId, $columns = ['*'])
    {
        return $this->select($columns)
            ->where('order_id', $orderId)
            ->findAll();
    }

    public function getOrderProducts($orderId)
{
    $products = $this->select('*') 
                ->where('order_id', $orderId)
                ->findAll();

    // Apply VAT calculation for each product
    $result = [];
    foreach ($products as $product) {
        $vatRate = $product['tax_rate'] ?? 0;
        $grossPrice = $product['price_brutto'] ?? 0;
        
        // Net price calculation
        $product['price_netto'] = round($grossPrice / (1 + $vatRate / 100), 4);
        $product['price_total_netto'] = round($this->calculatePriceWithoutVAT($product['price_brutto'], $product['tax_rate'], $product['quantity']), 4);
        $product['price_total_brutto'] = round($this->calculatePriceWithVAT($product['price_brutto'], $product['quantity']), 4);
        $result[] = $product;
    }

    return $result;
}
    
    
    
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

    public function modifyProductValues($requestData)
{
    $OrderAllocation = new \App\Models\Api\Orders\OrderAllocation();
    $orderModel = new OrdersModel();

    $order = $orderModel->find($requestData["order_id"]);
    $orderProduct = $this->find($requestData["product_row_id"]);

    if ($orderProduct !== null) {
        $orderProductId = $orderProduct["order_product_id"];

    if ($order === null) {
        // The order does not exist
        return [
            "order_id" => $requestData["order_id"],
            "status" => "missing",
        ];
    }

    if ($order["order_status"] === 'locked') {
        // The order is locked, prevent updates
        return [
            "status" => "locked",
            "order_id" => $requestData["order_id"],
        ];
    }

    $response = ["order_id" => $requestData["order_id"]];

    if (isset($requestData["quantity"])) {
        

        $response["operation_success"][] = $OrderAllocation->modifyAllocatedQuantityOrderProduct($orderProduct["quantity"],$requestData["quantity"],$orderProduct["order_product_id"],$orderProduct["product_id"],$requestData["order_id"]);

        $this->set("quantity", $requestData["quantity"])
            ->where("id", $requestData["product_row_id"])
            ->update();
        
        $response["operation_success"][]= ["order_quantity_change" => "success"];
    }

    if (isset($requestData["price_brutto"])) {
        $this->set("price_brutto", $requestData["price_brutto"])
            ->where("id", $requestData["product_row_id"])
            ->update();

        $response["order_price_change"] = "success";
    }

    return $response;
}
}

    
}