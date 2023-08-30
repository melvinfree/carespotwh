<?php

namespace App\Models\Api\Inventory;

use CodeIgniter\Model;

class TransferProductModel extends Model
{
    protected $table = 'ci_transfers_products';
    protected $primaryKey = 'id';

    protected $allowedFields = ["quantity"];


    public function getTransferProducts($transfer_id)
    {
        $products = $this->select('*') 
                    ->where('transfer_id', $transfer_id)
                    ->findAll();
    
        
        $resultCount = count($products);

        if($resultCount > 0){
            return $products;
        }
        else{
            return ["products" => 0];
        }
    }

}