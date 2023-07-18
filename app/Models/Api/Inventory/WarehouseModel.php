<?php

namespace App\Models\Api\Inventory;

use CodeIgniter\Model;

class WarehouseModel extends Model
{

    protected $table = 'warehouses';
    protected $primaryKey = 'id';


    public function getWarehouseList()
    {
    $products = $this->select('id, name') 
                ->where('status', 'active')
                ->findAll();

    return $products;
    }

}