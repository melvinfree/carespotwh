<?php

namespace App\Models\Api\Inventory;

use CodeIgniter\Model;

class TransfersModel extends Model
{

    protected $table = 'ci_transfers';
    protected $primaryKey = 'id';


    // Returning order list (including total with vat and without)
    // Used in Orders Controller for Endpoint "getAll"
    public function getInfo()
    {

        $warehouses = $this->getWarehouseList(); // Fetching warehouse list from database

        $prepareArray = [
            "warehouses" => $warehouses
        ];
        
        return $prepareArray;
    }

    public function createTransfer($data)
    {
        $this->db->table($this->table)->insert($data);
        return $this->db->insertID(); // returns the ID of the inserted record
    }

}