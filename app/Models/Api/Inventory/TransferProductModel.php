<?php

namespace App\Models\Api\Inventory;

use CodeIgniter\Model;
use App\Models\Api\Inventory\ProductsModel;
use App\Models\Api\Inventory\TransfersModel;
use App\Models\Api\Inventory\StockModel;

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

        $stockModel = new StockModel();
        $transferModel = new TransfersModel();

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

    public function transfersProductsList($transfer_id){

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
            ci_transfers ON ci_transfers.id = ci_transfers_products.transfer_id
        JOIN
            stock_copy1 ON stock_copy1.product_transfer_id = ci_transfers_products.id    
        WHERE 
        ci_transfers_products.transfer_id = ?
        GROUP BY 
        ci_transfers_products.id,
        ci_transfers_products.product_id,
        ci_transfers_products.product_name", 
        [$transfer_id]
    );

    $products = $query->getResultArray();

    foreach($products as &$product){
        $product['not_picked_quantity'] = $product['total_quantity'] - $product['picked_quantity'];
    }

    return $products;

    }

    public function processProduct($ean_code,$transfer_id){
        $TransfersModel = new TransfersModel();

        $transfer = $TransfersModel->find($transfer_id);
        

        if(!$transfer){
            return ["error" => true, "message" => "This transfer cannot be identified"];
        }
        
        $eanExist = $this->db->table('ci_product_eans')
            ->where('ean', $ean_code)
            ->get()
            ->getRow();
        

            if(!$eanExist){
                return ['error' => true, 'ean_status' => 'EAN-ul inserat nu exista in baza de date, te rog sa il adaugi produsului corespondent'];
            }

            $ProductsModel = new ProductsModel();
            $product_name = $ProductsModel->findProductNamebyId($eanExist->product_id);

            $stock_row = $this->db->table('stock_copy1')
                ->where('product_id', $eanExist->product_id)
                ->where('transfer_id', $transfer_id)
                ->where('warehouse', 10)
                ->where('transfer_status', null)
                ->orWhere('transfer_status', 'new')
                ->get()
                ->getRow();

                if(!$stock_row){
                    return ['already_picked' => 1, 'message' => 'This product was already marked as picked'];
                }

            if ($eanExist){

                $countBeforeAdd = $this->db->table('stock_copy1')
                    ->where('product_id', $eanExist->product_id)
                    ->where('transfer_id', $transfer_id)
                    ->where('warehouse', 10)
                    ->where('transfer_status', null)
                    ->orWhere('transfer_status', 'new')
                    ->countAllResults();

                if($countBeforeAdd <= 0){

                    return ['row_id' => $stock_row->id, 'already_picked' => 1, 'message' => 'This product was already marked as picked'];

                }
            
                $insert_data_stock = [
                    'transfer_status' => 'ready',
                ];

                $this->db->table('stock_copy1')
                ->set($insert_data_stock)
                ->where('id', $stock_row->id)
                ->update();


                $count = $this->db->table('stock_copy1')
                    ->where('product_id', $eanExist->product_id)
                    ->where('transfer_id', $transfer_id)
                    ->where('warehouse', 10)
                    ->where('transfer_status', null)
                    ->orWhere('transfer_status', 'new')
                    ->countAllResults();
                
                $return = ['row_id' => $stock_row->id, 'ean_exist' => 1, 'message' => 'Product succesfully marked as picked', 'remains_to_be_picked' => $count];
               
                return $return;

            }

            }

}