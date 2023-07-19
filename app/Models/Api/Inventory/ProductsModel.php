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
        $this->select('id, name');
        $this->where('status', 'active');
        $this->like('id', $searchTerm)
         ->orLike('model', $searchTerm)
         ->orLike('name', $searchTerm);
        $this->orderBy('id', 'DESC');
        $query = $this->get(); 
        $products = $query->getResultArray(); 

        return $products;
    }

}