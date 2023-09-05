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
    
    
    public function processProductPacking($data)
{
    $OrderModel = new OrdersModel();
    $TransfersModel = new TransfersModel();

    if (isset($data["order_id"])) {
        return $this->processProductPackingForEntity($data, $OrderModel, 'order_id');
    } elseif (isset($data["transfer_id"])) {
        return $this->processProductPackingForEntity($data, $TransfersModel, 'transfer_id');
    }

    return ['error' => true, 'message' => 'Invalid data provided'];
}

private function processProductPackingForEntity($data, $entityModel, $idField)
{
    $entity = $entityModel->find($data[$idField]);

    if (!$entity) {
        return ["error" => true, "message" => ucfirst($idField) . " ID " . $data[$idField] . " cannot be identified"];
    }

    if (isset($data['product_id'])) {
        return $this->processProductPackingByProduct($data, $entityModel, $idField);
    }

    $eanExist = $this->db->table('stock_copy1')
        ->where($idField, $data[$idField])
        ->where('ean', $data['ean_code'])
        ->where('box_id', null)
        ->where('packed', 0)
        ->get()
        ->getRow();

    if (!$eanExist) {
        return ['error' => true, 'ean_status' => 'EAN-ul inserat nu este alocat pe produsul din ' . $idField];
    }

    $stock_row = $this->db->table('stock_copy1')
        ->where('product_id', $eanExist->product_id)
        ->where('ean', $eanExist->ean)
        ->where($idField, $data[$idField])
        ->where('box_id', null)
        ->where('packed', 0)
        ->get()
        ->getRow();

    if (!$stock_row) {
        return ['already_packed' => 1, 'message' => 'This product was already marked as packed'];
    }

    $update_data_stock = [
        'transfer_status' => 'ready',
        'box_id' => $data['box_id'],
        'packed' => 1,
    ];

    // Prepare data to be inserted in order_boxes_items
    $insert_data_items = [
        'box_id' => $data['box_id'] ?? '',
        $idField => $data[$idField] ?? '',
        $idField . '_product_id' => $stock_row->{$idField . '_product_id'} ?? '',
        'stock_id' => $stock_row->id ?? '',
        'product_id' => $stock_row->product_id ?? '',
        'ean' => $stock_row->ean ?? '',
    ];

    $this->db->table($this->table)->insert($insert_data_items);

    $this->db->table('stock_copy1')
        ->set($update_data_stock)
        ->where('id', $stock_row->id)
        ->update();

    $count = $this->db->table('stock_copy1')
        ->where('product_id', $eanExist->product_id)
        ->where($idField, $data[$idField])
        ->where('box_id', null)
        ->where('packed', 0)
        ->countAllResults();

    return [
        'row_id' => $stock_row->id,
        'ean_exist' => 1,
        'message' => 'Product successfully marked as packed',
        'remains_to_be_packed' => $count,
    ];
}

private function processProductPackingByProduct($data, $entityModel, $idField)
{
    $stock_row = $this->db->table('stock_copy1')
        ->where('product_id', $data['product_id'])
        ->where($idField, $data[$idField])
        ->where('box_id', null)
        ->where('packed', 0)
        ->get()
        ->getRow();

    if (!$stock_row) {
        return ['already_packed' => 1, 'message' => 'This product was already marked as packed'];
    }

    $update_data_stock = [
        'transfer_status' => 'ready',
        'box_id' => $data['box_id'],
        'packed' => 1,
    ];

    // Prepare data to be inserted in order_boxes_items
    $insert_data_items = [
        'box_id' => $data['box_id'] ?? '',
        $idField => $data[$idField] ?? '',
        $idField . '_product_id' => $stock_row->{$idField . '_product_id'} ?? '',
        'stock_id' => $stock_row->id ?? '',
        'product_id' => $stock_row->product_id ?? '',
        'ean' => $stock_row->ean ?? '',
    ];

    $this->db->table($this->table)->insert($insert_data_items);

    $this->db->table('stock_copy1')
        ->set($update_data_stock)
        ->where('id', $stock_row->id)
        ->update();

    $count = $this->db->table('stock_copy1')
        ->where('product_id', $data['product_id'])
        ->where($idField, $data[$idField])
        ->where('box_id', null)
        ->where('packed', 0)
        ->countAllResults();

    return [
        'row_id' => $stock_row->id,
        'ean_exist' => 1,
        'message' => 'Product successfully marked as packed',
        'remains_to_be_packed' => $count,
    ];
}




            public function processProductPicking($data)
            {
                $OrderModel = new OrdersModel();
                $TransfersModel = new TransfersModel();
                
                if (isset($data["order_id"])) {
                    return $this->processProductPickingForEntity($data, $OrderModel, 'order_id');
                } elseif (isset($data["transfer_id"])) {
                    return $this->processProductPickingForEntity($data, $TransfersModel, 'transfer_id');
                }
            
                return ['error' => true, 'message' => 'Invalid data provided'];
            }
            
            private function processProductPickingForEntity($data, $entityModel, $idField)
            {
                $entity = $entityModel->find($data[$idField]);
                
                if (!$entity) {
                    return ["error" => true, "message" => ucfirst($idField) . " ID " . $data[$idField] . " cannot be identified"];
                }
                
                if (isset($data['product_id'])) {
                    return $this->processProductPickingByProduct($data, $entityModel, $idField);
                }
            
                $eanExist = $this->db->table('stock_copy1')
                    ->where($idField, $data[$idField])
                    ->where('ean', $data['ean_code'])
                    ->where('picked', 0)
                    ->get()
                    ->getRow();
            
                if (!$eanExist) {
                    return ['error' => true, 'ean_status' => 'EAN-ul inserat nu este alocat sau a fost scanata cantitatea commpleta ' . $data[$idField]];
                }
            
                $stock_row = $this->db->table('stock_copy1')
                    ->where('product_id', $eanExist->product_id)
                    ->where('ean', $eanExist->ean)
                    ->where($idField, $data[$idField])
                    ->where('picked', 0)
                    ->get()
                    ->getRow();
            
                if (!$stock_row) {
                    return ['already_picked' => 1, 'message' => 'This product was already marked as picked'];
                }
            
                $update_data_stock = [
                    'picked' => 1,
                ];
            
                $this->db->table('stock_copy1')
                    ->set($update_data_stock)
                    ->where('id', $stock_row->id)
                    ->update();
            
                $count = $this->db->table('stock_copy1')
                    ->where('product_id', $eanExist->product_id)
                    ->where($idField, $data[$idField])
                    ->where('picked', 0)
                    ->countAllResults();
            
                return ['row_id' => $stock_row->id, 'ean_exist' => 1, 'message' => 'Product successfully marked as picked', 'remains_to_be_picked' => $count];
            }
            
            private function processProductPickingByProduct($data, $entityModel, $idField)
            {
                $stock_row = $this->db->table('stock_copy1')
                    ->where('product_id', $data['product_id'])
                    ->where($idField, $data[$idField])
                    ->where('picked', 0)
                    ->get()
                    ->getRow();
            
                if (!$stock_row) {
                    return ['already_picked' => 1, 'message' => 'This product was already marked as picked'];
                }
            
                $update_data_stock = [
                    'picked' => 1,
                ];
            
                $this->db->table('stock_copy1')
                    ->set($update_data_stock)
                    ->where('id', $stock_row->id)
                    ->update();
            
                $count = $this->db->table('stock_copy1')
                    ->where('product_id', $data['product_id'])
                    ->where($idField, $data[$idField])
                    ->where('picked', 0)
                    ->countAllResults();
            
                return ['row_id' => $stock_row->id, 'ean_exist' => 1, 'message' => 'Product successfully marked as picked', 'remains_to_be_picked' => $count];
            }
                    

}