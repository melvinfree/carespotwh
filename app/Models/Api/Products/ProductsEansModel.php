<?php

namespace App\Models\Api\Products;

use CodeIgniter\Model;

class ProductsEansModel extends Model
{
    protected $table = "ci_product_eans";
    protected $primaryKey = "id";

    protected $allowedFields = ["product_id", "ean"];

    public function getEans($product_id){
        
        $this->where('product_id', $product_id);
        $count = $this->countAllResults();
        
        if($count < 1){
            return [
                'error' => true,
                'message' => 'This product do not have an ean code linked.'
            ];
        }
        
        $this->select('
        product_id,
        ean');

        $this->orderBy("id", "ASC");
        $query = $this->get();

        $product_eans = $query->getResultArray();

        return $product_eans;
    }



}