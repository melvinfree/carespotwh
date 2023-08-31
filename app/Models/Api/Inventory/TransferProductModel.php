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
                'status' => 'allocated_transfer',
                'transfer_status' => 'new'
            ];

            $response[] = $stockModel->addToTransfer($data);
            
        }

        return $response;
    }

    public function transfersProductsList($invoice_id){

        $query = $this->db->query("
        SELECT 
            ci_transfers_products.id AS row_id,
            ci_transfers_products.product_id,
            ci_transfers_products.product_name,
            COUNT(stock_copy1.id) as total_quantity,
            SUM(IF(stock_copy1.transfer_status = 'ready', 1, 0)) as picked_quantity
        FROM 
        ci_transfers_products
        JOIN 
            ci_transfers ON ci_transfers.id = ci_transfers_products.invoice_id
        JOIN
            stock_copy1 ON stock_copy1.product_transfer_id = ci_transfers_products.id    
        WHERE 
        ci_transfers_products.invoice_id = ?
        GROUP BY 
        ci_transfers_products.id,
        ci_transfers_products.product_id,
        ci_transfers_products.product_name", 
        [$invoice_id]
    );

    $products = $query->getResultArray();

    foreach($products as &$product){
        $product['not_picked_quantity'] = $product['total_quantity'] - $product['picked_quantity'];
    }

    return $products;

    }

}