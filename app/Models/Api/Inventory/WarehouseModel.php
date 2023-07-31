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

    public function transfersList($limit, $offset)
    {
        $this->select('
        id,
        old_warehouse_name,
        new_warehouse_name,
        status,
        confirmed');

        $this->orderBy("id", "DESC");
        $this->limit($limit, $offset);
        $query = $this->get();
        $transfers = $query->getResultArray();

        return $transfers;

    }

}