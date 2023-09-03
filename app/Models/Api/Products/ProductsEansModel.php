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
        id as row_id,
        product_id,
        ean');

        $this->where("product_id", $product_id);
        $query = $this->get();

        $product_eans = $query->getResultArray();

        return $product_eans;
    }

    public function deleteEan($rowId){

        if($this->delete(['id' => $rowId])){
            return ["error" => false, "message" => "Selected ean was deleted"];
        }
        else{
            return ["error" => true, "message" => "Ean cannot be deleted"];
        }
    }

    public function addEan($product_id,$ean_code){

        $this->insert([
            'product_id' => $product_id,
            'ean' => $ean_code
        ]);

        return ['record_id' => $this->db->insertID()];

    }



}