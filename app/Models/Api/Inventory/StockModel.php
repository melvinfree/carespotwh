<?php

namespace App\Models\Api\Inventory;

use CodeIgniter\Model;

class StockModel extends Model
{

    protected $table = 'stock_copy1';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'product_id',
        'supplier',
        'quantity',
        'invoice_in_id',
        'invoice_product_id',
        'warehouse',
        'discount',
        'status',
        'acquisition_price',
        'acquisition_price_invoice'
    ];

    public function addToStock($data)
    {
        
        for($i = 0; $i < $data['quantity']; $i++) {
            $this->insert([
                'product_id' => $data['product_id'],
                'supplier' => $data['supplier'],
                'quantity' => $data['quantity'],
                'invoice_in_id' => $data['invoice_in_id'],
                'invoice_product_id' => $data['invoice_product_id'],
                'warehouse' => $data['warehouse'],
                'discount' => $data['discount'],  
                'status' => $data['status'],
                'acquisition_price' => $data['acquisition_price'],
                'acquisition_price_invoice' => $data['acquisition_price_invoice'],
            ]);
        }
    }

}