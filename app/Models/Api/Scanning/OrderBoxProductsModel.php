<?php

namespace App\Models\Api\Scanning;

use CodeIgniter\Model;
use App\Models\Api\Orders\OrdersModel;
use App\Models\Api\Orders\OrderProductsModel;
use App\Models\Api\Inventory\TransfersModel;
use App\Models\Api\Inventory\TransferProductModel;

class OrderBoxProductsModel extends Model
{
    
    protected $table = "order_boxes_items";
    protected $primaryKey = "id";

    protected $allowedFields = ["created_at"];
    
    // Fields; order_id || transfer_id && ean_code && box_id
    public function processProduct($data){
        
        $OrderModel = new OrdersModel();
        $TransfersModel = new TransfersModel();

        
        // Process products for orders
        /*if (isset($requestData["order_id"])){
            $order = $OrderModel->find($data['order_id']);

            if(!$order){
                return ["error" => true, "message" => "Order ID ".$data['order_id']." cannot be identified"];
            }

            $eanExist = $this->db->table('stock_copy1')
                                 ->where('order_id', $data['transfer_id'])
                                 ->where('ean', $data['ean_code'])
                                 ->where('box_id', null)
                                 ->where('picked', 0)
                                 ->get()
                                 ->getRow();
        

            if(!$eanExist){
                return ['error' => true, 'ean_status' => 'EAN-ul inserat nu este alocat pe transferul din comanda'];
            }

            $stock_row = $this->db->table('stock_copy1')
                ->where('product_id', $eanExist->product_id)
                ->where('ean', $eanExist->ean)
                ->where('order_id', $data["order_id"])
                ->where('box_id', null)
                ->where('picked', 0)
                ->get()
                ->getRow();

                if(!$stock_row){
                    return ['already_picked' => 1, 'message' => 'This product was already marked as picked'];
                }

                if ($eanExist && $stock_row){
                
                    $update_data_stock = [
                        'transfer_status' => 'ready',
                        'box_id' => $data['box_id'],
                        'picked' => 1
                    ];
                    
                    // Prepare data to be inserted in order_boxes_items
                    $insert_data_items = [
                        'box_id' => $data['box_id'],
                        'transfer_id' => $data['transfer_id'],
                        'transfer_product_id' => $stock_row->product_transfer_id,
                        'stock_id' => $stock_row->id,
                        'product_id' => $stock_row->product_id,
                        'ean' => $stock_row->ean
                    ];   

                    $this->insert($insert_data_items);
    
                    $this->db->table('stock_copy1')
                    ->set($update_data_stock)
                    ->where('id', $stock_row->id)
                    ->update();
    
    
                    $count = $this->db->table('stock_copy1')
                        ->where('product_id', $eanExist->product_id)
                        ->where('transfer_id', $data["transfer_id"])
                        ->where('box_id', null)
                        ->where('picked', 0)
                        ->countAllResults();
                    
                    $return = ['row_id' => $stock_row->id, 'ean_exist' => 1, 'message' => 'Product succesfully marked as picked', 'remains_to_be_picked' => $count];
                   
                    return $return;
    
                }    
        } */
        
        
        // Process products for transfers

        if (isset($requestData["transfer_id"])){
            $transfer = $TransfersModel->find($data['transfer_id']);


            if(!$transfer){
                return ["error" => true, "message" => "Transfer ID ".$data['transfer_id']." cannot be identified"];
            }

            $eanExist = $this->db->table('stock_copy1')
                                 ->where('transfer_id', $data['transfer_id'])
                                 ->where('ean', $data['ean_code'])
                                 ->where('box_id', null)
                                 ->where('picked', 0)
                                 ->get()
                                 ->getRow();
        

            if(!$eanExist){
                return ['error' => true, 'ean_status' => 'EAN-ul inserat nu este alocat pe transferul din comanda'];
            }

            $stock_row = $this->db->table('stock_copy1')
                ->where('product_id', $eanExist->product_id)
                ->where('ean', $eanExist->ean)
                ->where('transfer_id', $data["transfer_id"])
                ->where('box_id', null)
                ->where('picked', 0)
                ->get()
                ->getRow();

                if(!$stock_row){
                    return ['already_picked' => 1, 'message' => 'This product was already marked as picked'];
                }

                if ($eanExist && $stock_row){
                
                    $update_data_stock = [
                        'transfer_status' => 'ready',
                        'box_id' => $data['box_id'],
                        'picked' => 1,
                    ];
                    
                    // Prepare data to be inserted in order_boxes_items
                    $insert_data_items = [
                        'box_id' => $data['box_id'],
                        'transfer_id' => $data['transfer_id'],
                        'transfer_product_id' => $stock_row->product_transfer_id,
                        'stock_id' => $stock_row->id,
                        'product_id' => $stock_row->product_id,
                        'ean' => $stock_row->ean
                    ];   

                    $this->insert($insert_data_items);
    
                    $this->db->table('stock_copy1')
                    ->set($update_data_stock)
                    ->where('id', $stock_row->id)
                    ->update();
    
    
                    $count = $this->db->table('stock_copy1')
                        ->where('product_id', $eanExist->product_id)
                        ->where('transfer_id', $data["transfer_id"])
                        ->where('box_id', null)
                        ->where('picked', 0)
                        ->countAllResults();
                    
                    $return = ['row_id' => $stock_row->id, 'ean_exist' => 1, 'message' => 'Product succesfully marked as picked', 'remains_to_be_picked' => $count];
                   
                    return $return;
    
                }    
        }
        else{
            return ["test" => true];
        }
        
        
    

            

            

            

            }

}