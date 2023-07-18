<?php

namespace App\Models\Api\Suppliers;

use CodeIgniter\Model;

class SuppliersModel extends Model
{

    protected $table = 'suppliers';
    protected $primaryKey = 'id';


    public function getSuppliersList()
    {
    $suppliers = $this->select('id, alias') 
                ->findAll();

    return $suppliers;
    }


}