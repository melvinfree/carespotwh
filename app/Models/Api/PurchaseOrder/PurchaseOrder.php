<?php

namespace App\Models\Api\PurchaseOrder;

use CodeIgniter\Model;

class PurchaseOrder extends Model
{

    protected $table = 'invoices_in';
    protected $primaryKey = 'id';


    // Returning order list (including total with vat and without)
    // Used in Orders Controller for Endpoint "getAll"
    public function getInfo()
    {


        $WarehouseModel = new \App\Models\Api\Inventory\WarehouseModel();
        $SupplierModel = new \App\Models\Api\Suppliers\SuppliersModel();

        $warehouses = $WarehouseModel->getWarehouseList(); // Fetching warehouse list from database
        $suppliers = $SupplierModel->getSuppliersList(); // Fetching suppliers list from database

        
        $prepareArray = [
            "suppliers" => $suppliers,
            "warehouses" => $warehouses
        ];

        return $prepareArray;
    }

}