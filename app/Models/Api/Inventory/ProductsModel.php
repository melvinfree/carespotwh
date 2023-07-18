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

}