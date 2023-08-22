<?php

namespace App\Models\Api\PurchaseOrder;

use CodeIgniter\Model;

class PurchaseOrderModel extends Model
{
    protected $table = "products";
    protected $primaryKey = "id";

    protected $allowedFields = ["name", "description"];



    public function getProductsListModel($limit, $offset)
    {
        $this->select('
        products.id,
        products.name,
        manufacturers.name,
        products.status
        ');

        $this->join(
            "manufacturers",
            "manufacturers.id = products.manufacturer",
            "left"
        );

        $this->orderBy("id", "DESC");
        $this->limit($limit, $offset);
        $query = $this->get();
        $products = $query->getResultArray();

        return $products;

    }

}