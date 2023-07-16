<?php

namespace App\Models\Api\Customers;

use CodeIgniter\Model;

class CDeliveryDataModel extends Model
{

    protected $table = 'ci_customer_delivery_data';
    protected $primaryKey = 'id';

    public function getDeliveryData($customerId)
    {
        return $this->select('*')
            ->where('customer_id', $customerId)
            ->findAll();
    }

}