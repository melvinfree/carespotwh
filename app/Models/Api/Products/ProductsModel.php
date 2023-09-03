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

    public function getProductDetails($product_id)
    {

        $productDetails = $this->find($product_id);

        if ($productDetails === null) {
            return [
                'error' => true,
                'message' => 'Product id not found'
            ];
        }

        $productEans = new \App\Models\Api\Products\ProductsEansModel();
        $manufacturers = new \App\Models\Api\Products\ManufacturersModel();
        
        $this->select('
        products.id,
        products.name as product_name,
        products.description,
        manufacturers.name as man_name,
        products.status
        ');

        $this->join(
            "manufacturers",
            "manufacturers.id = products.manufacturer",
            "left"
        );

        $this->where('products.id', $product_id);
        $query = $this->get();

        $product['product'] = $query->getResultArray();
        $product['product_ean_codes'] = $productEans->getEans($product_id);
        // $product['available_brands'] = $manufacturers->getAllManufacturersName();

        return $product;

    }

}