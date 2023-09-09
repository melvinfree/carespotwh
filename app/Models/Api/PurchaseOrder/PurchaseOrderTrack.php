<?php

namespace App\Models\Api\PurchaseOrder;

use CodeIgniter\Model;
use App\Models\Api\Inventory\ProductsModel;
use App\Models\Api\Inventory\StockModel;

class PurchaseOrderTrack extends Model
{


    protected $table = 'cs_purchase_order_track';
    protected $primaryKey = 'id';

    protected $allowedFields = ["status"];


    public function logReverseAction($stock_id,$actionType,$order_id,$invoice_out_id){
        
        $dbRecord = $this->db->table($this->table)
                            ->where('stock_id', $stock_id)
                            ->get()
                            ->getRow();

        if($dbRecord){

            $updateData = [
                'status' => $actionType,
            ];

            $this->db->table($this->table)
                ->where('stock_id', $stock_id)
                ->update($updateData);
        }
        else{

            $log_information = [
                'stock_id' => $stock_id,
                'status' => $actionType,
                'order_id' => $order_id ?? NULL,
                'invoice_out_id' => $invoice_out_id ?? NULL,
            ];
            
            $this->db->table($this->table)->insert($log_information);
        }

    }

    public function getInfoUndo($stock_id){
        
        $dbRecord = $this->db->table($this->table)
                            ->where('stock_id', $stock_id)
                            ->where('status', 'reversed')
                            ->get()
                            ->getRow();

        

        $returnData = [
            "order_id" => $dbRecord->order_id ?? "",
            "invoice_out_id" => $dbRecord->order_id ?? ""
        ];

        if(!$dbRecord){
            $returnData = [
                "order_id" => null,
                "invoice_out_id" => null
            ];
        }

        return $returnData;
    }
    

}