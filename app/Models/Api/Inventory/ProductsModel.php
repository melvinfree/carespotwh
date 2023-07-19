<?php

namespace App\Models\Api\Inventory;

use CodeIgniter\Model;

class ProductsModel extends Model
{

    protected $table = 'products';
    protected $primaryKey = 'id';


    public function getProductsList()
    {
    $products = $this->select('id, name') 
                ->where('status', 'active')
                ->findAll();

    return $products;
    }

    public function searchProducts($searchTerm)
{
    $searchTerm = "%{$searchTerm}%";  // Add wildcard characters for LIKE

    $query = $this->db->query("
        SELECT id, name, model
        FROM " . $this->table . "
        WHERE status = 'active' AND
            (id LIKE ?
            OR model LIKE ?
            OR name LIKE ?)
        ORDER BY 
            CASE 
                WHEN id LIKE ? THEN 1
                WHEN model LIKE ? THEN 2
                WHEN name LIKE ? THEN 3
                ELSE 4
            END, id DESC
        ", [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]
    );  // Pass search term into query multiple times

    $products = $query->getResultArray(); 

    return $products;
}

}