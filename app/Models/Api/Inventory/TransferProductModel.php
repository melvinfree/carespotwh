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

    public function blockQuantityTransferProducts($transfer_id)
    {
        $transferProducts = $this->where('transfer_id', $transfer_id)->findAll();

        $stockModel = new \App\Models\Api\Inventory\StockModel();
        $transferModel = new \App\Models\Api\Inventory\TransfersModel();

        $transfer = $transferModel->where('id', $transfer_id)->first();
        $old_warehouse_id = $transfer['old_warehouse'];
        $new_warehouse_id = $transfer['new_warehouse'];

        foreach ($transferProducts as $product) {

            
            $data = [
                'product_id' => $product['product_id'],
                'product_transfer_id' => $product['id'],
                'transfer_id' => $transfer_id,
                'quantity' => $product['quantity'],
                'old_warehouse' => $old_warehouse_id,
                'new_warehouse' => $new_warehouse_id,
                'status' => 'allocated_transfer'
            ];

        }
        
        return  $stockModel->addToTransfer($data);

       // return ["error"=> false, "message" => "success"];
    }

}