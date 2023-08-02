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

    public function findProductNamebyId($product_id)
    {
    $product = $this->select('name') 
                ->where('id', $product_id)
                ->first();

    // check if product was found
    if (!$product) {
        return null; // or return an error message or throw an exception
    }

    return $product['name']; // this will return the 'name' of the product
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