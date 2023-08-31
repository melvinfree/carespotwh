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
        'invoice_in_id',
        'invoice_product_id',
        'warehouse',
        'discount',
        'status',
        'acquisition_price',
        'acquisition_price_invoice',
        'ean',
        'product_transfer_id',
        'transfer_id',
        'reception_date'
    ];

    
    
    
    public function addToStock($data)
    {

        $countProductsAdded = $this->where('product_id', $data['product_id'])
                ->where('invoice_product_id', $data['invoice_product_id'])
                ->where('invoice_in_id', $data['invoice_in_id'])
                ->countAllResults();
        
        for($i = $countProductsAdded; $i < $data['quantity']; $i++) {
            

            $this->insert([
                'product_id' => $data['product_id'],
                'supplier' => $data['supplier'],
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

    public function addToTransfer($data)
{
    $countProductsAdded = $this->where('product_id', $data['product_id'])
        ->where('product_transfer_id', $data['product_transfer_id'])
        ->where('transfer_id', $data['transfer_id'])
        ->countAllResults();

    $rowsToSelect = $data['quantity'] - $countProductsAdded; 
    if ($rowsToSelect > 0) {     

        $query = $this->select('*')
            ->where('product_id', $data['product_id'])
            ->where('status', 'instock')
            ->where('warehouse', $data['old_warehouse'])
            ->limit($rowsToSelect)
            ->get(); // Get the query instance

        $rowsToUpdate = $query->getResultArray();
        
        $updatedRowCount = 0;
        foreach ($rowsToUpdate as $row) {
            $this->set([
                'transfer_id' => $data['transfer_id'],
                'product_transfer_id' => $data['product_transfer_id'],
                'status' => $data['status'],
                'transfer_status' => $data['transfer_status'],
                'warehouse' => $data['new_warehouse']
            ])
            ->where('id', $row['id'])
            ->update();
        
            $affectedRows = $this->affectedRows();
            if ($affectedRows > 0) {
                $updatedRowCount++;
                $updatedRowIds[] = $row['id'];
            }    
        }
        return ['product_id' => $data['product_id'], 'updated_rows' => $updatedRowCount, 'updated_rows_ids' => $updatedRowIds];
    } 
    else{
        return ['product_id' => $data['product_id'], 'updated_rows' => 0, 'updated_rows_ids' => false];
    }  
}

    public function getProductStockCount($productCode)
    {
        return $this->where('product_id', $productCode)
            ->where('status', 'instock')
            ->countAllResults();
    }


    private function getProductsAddedToStock($invoice_product_id,$invoice_in_id)
    {
        return $this->where('invoice_product_id', $invoice_product_id)
            ->where('invoice_in_id', $invoice_in_id)
            ->countAllResults();
    }


}