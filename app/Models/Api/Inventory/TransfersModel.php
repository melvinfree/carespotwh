<?php

namespace App\Models\Api\Inventory;

use CodeIgniter\Model;
use App\Models\Api\Inventory\TransferProductModel;

class TransfersModel extends Model
{

    protected $table = 'ci_transfers';

    protected $transfers_products_table = 'ci_transfers_products';
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

    public function getTransferInfo($transfer_id)
    {
        $transfer = $this->select('*')
                    ->where('id', $transfer_id)
                    ->first();

        if ($transfer) {

            $transferProductModel = new TransferProductModel();
            $transfer['transferProducts'] = $transferProductModel->getTransferProducts($transfer_id);
        }

        return $transfer;
    }

    public function searchProducts($searchTerm, $oldWarehouseId)
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
        ON " . $oldWarehouseId . " = warehouse.id
        WHERE product.status = 'active' 
        AND (product.id LIKE ?
        OR product.model LIKE ?
        OR product.name LIKE ?)
        GROUP BY product.id
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

// Adaugare produse pe nir
    // Daca produsul deja exista se actualizeaza valorile / daca nu, se adauga linie noua.
    public function insertProducts($data)
    {

        $transfer_row = $this->db->table($this->table)
        ->where('id', $data['transfer_id'])
        ->get()
        ->getRow();

        if($transfer_row->confirmed !== null){

            return ['error' => 1, 'already_confirmed' => 1, 'message' => 'You cannot add products into a transfer which was already confirmed'];
        }
        


    $responses = [];

    $transfer_id = $data['transfer_id'];

    foreach($data['products'] as $product) {
        $dbRecord = $this->db->table($this->transfers_products_table)
             ->where('transfer_id', $transfer_id)
             ->where('product_id', $product['product_id'])
             ->get()
             ->getRow();


        if($dbRecord) {

            $products = [
                'quantity' => $product['quantity']
            ];

            // Perform update operation and add the result to $responses
            $this->db->table($this->transfers_products_table)
                ->where('transfer_id', $transfer_id)
                ->where('product_id', $product['product_id'])
                ->update($products);

                if ($this->db->affectedRows() > 0) {
                    $updatedRecord = $this->db->table($this->transfers_products_table)
                                       ->where('transfer_id', $transfer_id)
                                       ->where('product_id', $product['product_id'])
                                       ->get()
                                       ->getRow();
                
                    $responses[] = ['record_id' => $updatedRecord->id];
                } else {
                    $responses[] = ['record_id' => "false"];
                }
        }
        else {


            // Perform insert operation and add the result to $responses
            
            $products = [
                'transfer_id' => $transfer_id,
                'product_id' => $product['product_id'],
                'product_name' => $product['product_name'],
                'quantity' => $product['quantity']
            ];
            
            $this->db->table($this->transfers_products_table)->insert($products);
            $responses[] = ['record_id' => $this->db->insertID()];
        }
    }

    return $responses;
    }

    

}