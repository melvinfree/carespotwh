<?php

namespace App\Models\Api\Inventory;

use CodeIgniter\Model;

class WarehouseModel extends Model
{

    protected $table = 'warehouses';
    protected $primaryKey = 'id';

    protected $allowedFields = ["name", "comments", "status", "allow_servicing_stock", "open_box", "location_id"];


    public function getWarehouseList()
    {
    $products = $this->select('id, name') 
                ->where('status', 'active')
                ->findAll();

    return $products;
    }

    public function whList($limit,$ofset)
    {
    $warehouses = $this->select('*') 
                 ->limit($limit,$ofset)
                ->findAll();

    return $warehouses;
    }

    public function getWHInfo($warehouse_id)
    {
        $warehouse = $this->select('*')
                    ->where('id', $warehouse_id)
                    ->first();

        if ($warehouse) {
            return $warehouse;
        }
        else{
            return ["error" => true, "message" => "This warehouse dosen't exist"];
        }
    }

    public function deleteWH($warehouse_id)
    {
        
        $stockModel = new \App\Models\Api\Inventory\StockModel();

        $warehouse = $this->find($warehouse_id);

        if(!$warehouse){
            return ["error" => true, "message" => "This warehouse does not exist"];
        }
        
        // Check if in that warehouse exist products, if yes, cannot be deleted
        $count_products = $stockModel->where('warehouse', $warehouse_id)
                                     ->countAllResults();

        if($count_products > 0){
            return ["error" => true, "message" => "This warehouse cannot be deleted because we have products there(sold or not sold)"];
        } 

        // Delete product from current NIR.
        $this->delete(['id' => $warehouse_id]);
        
        return ["error" => false, "message" => "Deleted succesfully"];


    }

    public function modifyWarehouse($requestData)
    {
        $warehouse = $this->find($requestData["warehouse_id"]);

        if ($warehouse === null) {
            // The invoice does not exist
            return [
                "error" => true,
                "warehouse_id" => $requestData["warehouse_id"],
                "message" => "This warehouse does not exist",
            ];
        }

        $response = ["warehouse_id" => $requestData["warehouse_id"]];

        if (isset($requestData["name"])) {
            $this->set("name", $requestData["name"])
                ->where("id", $requestData["warehouse_id"])
                ->update();

            $response["warehouse_name"] = ["error" => false, "message" => "Name updated"];
        }

        if (isset($requestData["comments"])) {
            $this->set("comments", $requestData["comments"])
                ->where("id", $requestData["warehouse_id"])
                ->update();

            $response["warehouse_comments"] = ["error" => false, "message" => "Comments updated"];
        }

        if (isset($requestData["status"])) {
            $this->set("status", $requestData["status"])
                ->where("id", $requestData["warehouse_id"])
                ->update();

            $response["warehouse_status"] = ["error" => false, "message" => "Status updated"];
        }

        if (isset($requestData["allow_servicing_stock"])) {
            $this->set("allow_servicing_stock", $requestData["allow_servicing_stock"])
                ->where("id", $requestData["warehouse_id"])
                ->update();

            $response["warehouse_allow_servicing_stock"] = ["error" => false, "message" => "allow_servicing_stock updated"];
        }
        if (isset($requestData["open_box"])) {
            $this->set("open_box", $requestData["open_box"])
                ->where("id", $requestData["warehouse_id"])
                ->update();

            $response["warehouse_open_box"] = ["error" => false, "message" => "open_box updated"];
        }

        if (isset($requestData["location_id"])) {
            $this->set("location_id", $requestData["location_id"])
                ->where("id", $requestData["warehouse_id"])
                ->update();

            $response["warehouse_location_id"] = ["error" => false, "message" => "location_id updated"];
        }

        return $response;
    }

    public function getAllowServicingStockWarehouseList()
    {
    $warehouses = $this->select('id, name') 
                ->where('status', 'active')
                ->where('allow_servicing_stock', 1)
                ->findAll();

    return $warehouses;
    }

    public function getAllowSellingWarehouseList()
    {
    $warehouses = $this->select('id, name') 
                ->where('status', 'active')
                ->where('allow_selling', 1)
                ->findAll();

    return $warehouses;
    }

    public function transfersList($limit, $offset)
    {
        $this->select('
        id,
        old_warehouse_name,
        new_warehouse_name,
        status,
        confirmed');

        $this->orderBy("id", "DESC");
        $this->limit($limit, $offset);
        $query = $this->get();
        $transfers = $query->getResultArray();

        return $transfers;

    }

}