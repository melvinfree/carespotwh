<?php

namespace App\Models\Api;

use CodeIgniter\Model;

class OrdersModel extends Model
{
    protected $table = 'ci_bl_orders'; // Replace with your actual table name

    public function getAll($limit, $offset)
    {
        // Perform the query using CodeIgniter's query builder
        $query = $this->table($this->table)
    ->join('ci_bl_order_products', 'ci_bl_orders.order_id', '=', 'order_products.order_id')
    ->limit($limit, $offset)
    ->select([
        'ci_bl_orders.id',
        'ci_bl_orders.date_add',
        'ci_bl_orders.invoice_company',
        'ci_bl_orders.status',
        'ci_bl_orders.warehouseStatus',
        DB::raw('(ci_bl_orders.delivery_price * (100 - ci_bl_order_products.tax) / 100) + SUM(ci_bl_order_products.price_brutto) as totalNoVat')
    ])
    ->groupBy('orders.order_id')
    ->get();

        // Check if the query was successful
        if ($query->resultID->num_rows > 0) {
            // Return the fetched results
            return $query->getResult();
        } else {
            // Return an empty array or handle the case when no results are found
            return [];
        }
    }
}