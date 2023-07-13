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
                      ->limit($limit, $offset)
                      ->select(['id', 'date_add'])
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