<?php

namespace App\Models\Api\Orders;
use App\Models\Api\Inventory\StockModel;

use CodeIgniter\Model;

class OrderAllocation extends Model
{
    protected $table = 'stock_copy1';
    protected $primaryKey = 'id';

    protected $allowedFields = ["quantity", "price_brutto"];

    public function modifyAllocatedQuantityOrderProduct($old_quantity,$new_quantity,$orderProductId,$orderId){

        $stockModel = new StockModel();

        // Stock decrease scenario (e.g. In order product quantity is 10 pcs, user adjust it to a smaller number, e.g. 8 pcs)       
        if($old_quantity > $new_quantity){ 

            // Summing the difference between old_quantity & new_quantity to see how many products we need to dealocate from order
            $difference = $old_quantity - $new_quantity;

            if($difference > 0){
                $rowsToUpdate = $stockModel->where('order_product_id', $orderProductId)
                    ->orderBy('id', 'ASC')
                    ->findAll($difference);

                foreach ($rowsToUpdate as $row) {
                    $sql = "
                    UPDATE " . $stockModel->table . "
                    SET order_product_id = NULL,
                        order_id = NULL,
                        status = 'instock'
                    WHERE id = ?
                    ";
            
                    $this->db->query($sql, [$row['id']]);
                }

                return ['error' => 0, "deallocated_quantity" => $difference];

            }

        }
        // Stock increase scenario (e.g. In order product quantity is 10 pcs, user adjust it to a greater number, e.g. 12 pcs) 
        elseif($new_quantity > $old_quantity){

            $difference = $new_quantity - $old_quantity;

            if($difference > 0){

                $count = $stockModel->where('order_product_id', $orderProductId)
                    ->where('warehouse', 1)
                    ->where('status', "instock")
                    ->countAllResults();

                    if($difference > $count){
                        return ['error' => 1, "available_quantity" => $count, 'needed_quantity' => $difference];
                    }
                
                $rowsToUpdate = $stockModel->where('order_product_id', $orderProductId)
                    ->where('warehouse', 1)
                    ->where('status', "instock")
                    ->orderBy('id', 'DESC')
                    ->findAll($difference);

                foreach ($rowsToUpdate as $row) {

                    $sql = "
                    UPDATE " . $stockModel->table . "
                    SET order_product_id = " . $orderProductId . ",
                        order_id = " . $orderId . ",
                        status = 'allocated'
                    WHERE id = ?
                    ";

                    $this->db->query($sql, [$row['id']]);
                }

                return ['error' => 0, "allocated_quantity" => $difference];


            }
        }

    }


}