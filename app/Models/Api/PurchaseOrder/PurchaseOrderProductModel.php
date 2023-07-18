<?php

namespace App\Models\Api\PurchaseOrder;

use CodeIgniter\Model;

class PurchaseOrderProductModel extends Model
{

    protected $table = 'invoices_in_products';
    protected $primaryKey = 'id';

    public function getPurchaseOrderValueNoVat($purchaseOrderId,$currency_rate){

        $query = $this->selectSumselectSum('(quantity * acquisition_price * ' . $currency_rate . ')', 'total_without_vat')
            ->where('invoice_id', $purchaseOrderId)
            ->get();

        if ($query->getNumRows() > 0) {
            $row = $query->getRow();
            return $row->total_without_vat;
        } else {
            return 0; // Return 0 if no rows found for the given invoice_id
        }
    }

    public function getPurchaseOrderValueWithVat($purchaseOrderId,$currency_rate){

        $query = $this->selectSum('(quantity * acquisition_price * tax * ' . $currency_rate . ')', 'total_with_vat')
            ->where('invoice_id', $purchaseOrderId)
            ->get();

        if ($query->getNumRows() > 0) {
            $row = $query->getRow();
            return $row->total_with_vat;
        } else {
            return 0; // Return 0 if no rows found for the given invoice_id
        }
    }

    }

