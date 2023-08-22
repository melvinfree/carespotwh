<?php

namespace App\Models\Api\Products;

use CodeIgniter\Model;

class ProductsModel extends Model
{
    protected $table = "products";
    protected $primaryKey = "id";

    protected $allowedFields = ["name", "description"];



    public function getProductsListModel($limit, $offset)
    {
        $this->select('
        products.id,
        products.name as product_name,
        manufacturers.name as man_name,
        products.status
        ');

        $this->join(
            "manufacturers",
            "manufacturers.id = products.manufacturer",
            "left"
        );

        $this->orderBy("id", "ASC");
        $this->limit($limit, $offset);
        $query = $this->get();
        $products = $query->getResultArray();

        return $products;

    }

}