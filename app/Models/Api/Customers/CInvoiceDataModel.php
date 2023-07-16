<?php

namespace App\Models\Api\Customers;

use CodeIgniter\Model;

class CInvoiceDataModel extends Model
{

    protected $table = 'ci_customer_invoice_data';
    protected $primaryKey = 'id';

    public function getInvoiceData($customerId)
    {
        return $this->select('*')
            ->where('customer_id', $customerId)
            ->findAll();
    }

}