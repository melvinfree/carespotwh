<?php

namespace App\Models\Api\Scanning;
use App\Models\Api\Orders\OrdersModel;
use App\Models\Api\Orders\OrderProductsModel;
use App\Models\Api\Inventory\TransfersModel;
use App\Models\Api\Inventory\TransferProductModel;
use App\Models\Api\Scanning\OrderBoxProductsModel;

use CodeIgniter\Model;

class OrderBoxesModel extends Model
{
    protected $table = "order_boxes";
    protected $primaryKey = "id";

    protected $allowedFields = ["box_barcode"];


    
    // For this function the input it will be either order_id or transfer_id
    public function addBox($data){

        $OrderModel = new OrdersModel();
        $TransfersModel = new TransfersModel();
        
    if (isset($data["order_id"])){
        $order = $OrderModel->find($data['order_id']);

        if(!$order){
            return ["error" => true, "message" => "Order ID ".$data['order_id']." cannot be identified"];
        }

        try {
            $insert = $this->insert([
                'order_id' => $data['order_id'],
            ]);
        
            if ($insert) {
                return ["error" => false, "message" => "Box ID " . $this->db->insertID() . " was inserted"];
            }
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            // Handle the database exception here
            return ["error" => true, "message" => "Database error: " . $e->getMessage()];
        }
        
        return ["error" => true, "message" => "Cannot perform insert operation"];
    }

    if (isset($data["transfer_id"])){
        $transfer = $TransfersModel->find($data['transfer_id']);

        if(!$TransfersModel){
            return ["error" => true, "message" => "Order ID ".$data['transfer_id']." cannot be identified"];
        }

        try {
            $insert = $this->insert([
                'transfer_id' => $data['transfer_id'],
            ]);
        
            if ($insert) {
                return ["error" => false, "message" => "Box ID " . $this->db->insertID() . " was inserted"];
            }
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            // Handle the database exception here
            return ["error" => true, "message" => "Database error: " . $e->getMessage()];
        }
        
        return ["error" => true, "message" => "Cannot perform insert operation"];
    }

        
    }

    // For this function the input it will be either order_id or transfer_id
    public function getBoxList($data)
    {
     
     // Get Boxes based on transfer_id
     if (isset($data["transfer_id"])){ 

        $box_list = $this->select('id, box_barcode, prods_in_box, weight') 
                ->where('transfer_id', $data['transfer_id'])
                ->findAll();
     }

     // Get Boxes based on order_id
     if (isset($data["order_id"])){

        $box_list = $this->select('id, box_barcode, prods_in_box, weight') 
                ->where('order_id', $data['order_id'])
                ->findAll();
     }
    
     // Return error if both was selected.
     if (isset($data["order_id"]) && isset($data["transfer_id"])){

        $box_list = ["error" => true, "message" => "Please, select order_id or transfer_id, not both"];
     }

    return $box_list;

    }

    public function deleteBox($box_id){

        $OrderBoxProductsModel = new OrderBoxProductsModel();

        $box = $this->find($box_id);

        if(!$box){
            return ["error" => true, "message" => "BOX ID ".$box_id." cannot be identified"];
        }

        $count_products_in_box = $OrderBoxProductsModel->where('box_id', $box_id)->countAllResults();

        if($count_products_in_box > 0){
            return ["error" => true, "message" => "Box ID ".$box_id." cannot be deleted because it has items added"];
        }

        // Delete product from current NIR.
        $this->delete(['id' => $box_id]);
        
        return ["error" => false, "message" => "Deleted succesfully"];

    }

}