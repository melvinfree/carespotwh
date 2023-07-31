<?php

namespace App\Models\Api\Inventory;

use CodeIgniter\Model;

class TransfersModel extends Model
{

    protected $table = 'ci_transfers';
    protected $primaryKey = 'id';


    // Returning order list (including total with vat and without)
    // Used in Orders Controller for Endpoint "getAll"
    public function getInfo()
    {
        $WarehouseModel = new \App\Models\Api\Inventory\WarehouseModel();
        
        $warehouses = $WarehouseModel->getWarehouseList(); // Fetching warehouse list from database

        $prepareArray = [
            "warehouses" => $warehouses
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

    public function searchProducts($searchTerm)
{
    $searchTerm = "%{$searchTerm}%";  // Add wildcard characters for LIKE

    $query = $this->db->query("
        SELECT 
            product.id, 
            product.name, 
            product.model, 
            COUNT(stock.id) as quantity
        FROM " . $this->table . " AS product
        LEFT JOIN stock_copy1 AS stock 
        ON product.id = stock.product_id 
        AND stock.status = 'instock' 
        AND stock.warehouse = 1
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