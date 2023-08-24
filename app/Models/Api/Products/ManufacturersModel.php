<?php

namespace App\Models\Api\Products;

use CodeIgniter\Model;

class ManufacturersModel extends Model
{
    protected $table = "manufacturers";
    protected $primaryKey = "id";



    public function getAllManufacturersName() {

        $this->select('id, name');

        $this->orderBy("id", "DESC");
        $query = $this->get();

        $manufacturers = $query->getResultArray();

        return $manufacturers;

    }

}