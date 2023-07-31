<?php

namespace App\Models\Api\Inventory;

use CodeIgniter\Model;

class TransfersModel extends Model
{

    protected $table = 'ci_transfers';
    protected $productTable = 'products';
    protected $primaryKey = 'id';


    // Returning order list (including total with vat and without)
    // Used in Orders Controller for Endpoint "getAll"
    public function getInfo()
    {
        $WarehouseModel = new \App\Models\Api\Inventory\WarehouseModel();
        
        $getAllowServicingStockWarehouseList = $WarehouseModel->getAllowServicingStockWarehouseList(); // Fetching Warehouse which allow to transfer stock from

        $getAllowSellingWarehouseList = $WarehouseModel->getAllowSellingWarehouseList(); // Fetching Warehouse which allow to sell prods from

        $prepareArray = [
            "warehouses_allow_servicing_stock" => $getAllowServicingStockWarehouseList,
            "warehouses_allow_selling_stock" => $getAllowSellingWarehouseList
        ];
        
        return $prepareArray;
    }

    public function createTransfer($data)
    {
        $this->db->table($this->table)->insert($data);
        return $this->db->insertID(); // returns the ID of the inserted record
    }

    public function getTransferList($limit, $offset)
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

    public function searchProducts($searchTerm,$oldWarehouseId)
    { 
    $searchTerm = "%{$searchTerm}%";  // Add wildcard characters for LIKE

    $query = $this->db->query("
        SELECT 
            product.id, 
            product.name, 
            product.model, 
            COUNT(stock.id) as available_quantity,
            warehouse.name as warehouse_name
        FROM " . $this->productTable . " AS product
        LEFT JOIN stock_copy1 AS stock 
        ON product.id = stock.product_id 
        AND stock.status = 'instock' 
        AND stock.warehouse = " . $oldWarehouseId . "
        LEFT JOIN warehouses AS warehouse
        ON stock.warehouse = warehouse.id
        WHERE product.status = 'active' 
        AND (product.id LIKE ?
        OR product.model LIKE ?
        OR product.name LIKE ?)
        GROUP BY product.id, stock.warehouse
        ORDER BY 
            CASE 
                WHEN product.id LIKE ? THEN 1
                WHEN product.model LIKE ? THEN 2
                WHEN product.name LIKE ? THEN 3
                ELSE 4
            END, product.id DESC
        ", [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]
    );  // Pass search term into query multiple times

    $products = $query->getResultArray(); 

    return $products;
    }

    

}