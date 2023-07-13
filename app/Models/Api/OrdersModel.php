<?php

namespace App\Models\Api;

use CodeIgniter\Model;

class OrdersModel extends Model
{
    protected $table = 'ci_bl_orders'; // Replace with your actual table name

    public function getAll($limit, $offset)
    {
        // Perform the query using CodeIgniter's query builder
        $this->db->select('ci_bl_orders.id, ci_bl_orders.date_add, ci_bl_orders.invoice_company');
        $this->db->select('(ci_bl_orders.delivery_price * (100 - ci_bl_order_products.tax) / 100) + SUM(ci_bl_order_products.price_brutto) as totalNoVat');
        $this->db->from($this->table);
        $this->db->join('ci_bl_order_products', 'ci_bl_orders.order_id = ci_bl_order_products.order_id');
        $this->db->group_by('ci_bl_orders.order_id');
        $this->db->limit($limit, $offset);
        $query = $this->db->get();

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