<?php

namespace App\Models\Api\Inventory;

use CodeIgniter\Model;
use App\Models\Api\Inventory\ProductsModel;

class ReceptionsModel extends Model
{

    protected $table = 'invoices_in'; // need to change the table here.
    protected $primaryKey = 'id';


    // This function it will be used to display receptions list inside Inventory Controller.
    // This function it will return all receptions which wasn't con med(receptioned in warehouse) and which are locked(nir was closed)
    // limit and offset used for pagination
    
    public function receptionsList($limit, $offset)
    {
        $this->select('
        invoices_in.id,
        invoices_in.invoice_date,
        invoices_in.invoice_series,
        invoices_in.number AS invoice_number,
        suppliers.name AS supplier_name,
        invoices_in.currency,
        COUNT(stock_copy1.id) as total_quantity,
        SUM(IF(stock_copy1.reception_date IS NOT NULL, 1, 0)) as receptioned_quantity');
    
        $this->join(
            "suppliers",
            "suppliers.id = invoices_in.supplier_id",
            "left"
        );
        $this->join(
            "transport",
            "transport.id = invoices_in.transport",
            "left"
        );
        $this->join(
            "invoices_in_products",
            "invoices_in_products.invoice_id = invoices_in.id",
            "left"
        );
        $this->join(
            "stock_copy1",
            "stock_copy1.invoice_product_id = invoices_in_products.id",
            "left"
        );
        $this->where('invoices_in.locked', 1);
        $this->where('invoices_in.confirmed', 0);
        $this->groupBy("invoices_in.id");
        $this->orderBy("invoices_in.id", "DESC");
        $this->limit($limit, $offset);
        $query = $this->get();
        $receptions = $query->getResultArray();
    
        $filteredReceptions = [];
        foreach ($receptions as $reception) {
            $reception['not_receptioned_quantity'] = $reception['total_quantity'] - $reception['receptioned_quantity'];
    
            // Calculating invoice value in a separate query
            $invoice_value_query = $this->db->query("SELECT SUM(acquisition_price * quantity) as invoice_value FROM invoices_in_products WHERE invoice_id = ?", $reception['id']);
            $invoice_value_result = $invoice_value_query->getRowArray();
            $reception['invoice_value'] = $invoice_value_result['invoice_value'];
    
            if ($reception['invoice_value'] > 0) {
                $filteredReceptions[] = $reception;
            }
        }
    
        return $filteredReceptions;
    }

    public function receptionsProductsList($invoice_id){

        $query = $this->db->query("
        SELECT 
            invoices_in_products.id AS row_id,
            invoices_in_products.product_id,
            invoices_in_products.product_name,
            COUNT(stock_copy1.id) as total_quantity,
            SUM(IF(stock_copy1.reception_date IS NOT NULL AND warehouse = 1, 1, 0)) as receptioned_quantity
        FROM 
            invoices_in_products
        JOIN 
            invoices_in ON invoices_in.id = invoices_in_products.invoice_id
        JOIN
            stock_copy1 ON stock_copy1.invoice_product_id = invoices_in_products.id    
        WHERE 
            invoices_in_products.invoice_id = ?
        GROUP BY 
            invoices_in_products.id,
            invoices_in_products.product_id,
            invoices_in_products.product_name", 
        [$invoice_id]
    );

    $products = $query->getResultArray();

    foreach($products as &$product){

        $product['not_confirmed_quantity'] = $product['total_quantity'] - $product['receptioned_quantity'];
    }

    return $products;

    }

    public function notConfirmedProductPcs($rowId){

        $this->select('
         stock_copy1.id,
         invoices_in_products.product_name,
         invoices_in_products.product_id
       ');
        $this->from('stock_copy1');
        $this->join(
            "invoices_in_products",
            "invoices_in_products.id = stock_copy1.invoice_product_id",
            "left"
        );

        $this->where('stock_copy1.reception_date', null);
        $this->where('invoices_in_products.id', $rowId);
        $this->where('stock_copy1.invoice_product_id', $rowId);
        $this->groupBy("stock_copy1.id");
        $query = $this->get();
        $result = $query->getResultArray();

        return $result;    

    }

    public function ConfirmedProductPcs($rowId){

        $this->select('
         stock_copy1.id,
         invoices_in_products.product_name,
         invoices_in_products.product_id
       ');
        $this->from('stock_copy1');
        $this->join(
            "invoices_in_products",
            "invoices_in_products.id = stock_copy1.invoice_product_id",
            "left"
        );

        $this->where('stock_copy1.reception_date IS NOT NULL');
        $this->where('invoices_in_products.id', $rowId);
        $this->where('stock_copy1.invoice_product_id', $rowId);
        $this->groupBy("stock_copy1.id");
        $query = $this->get();
        $result = $query->getResultArray();

        return $result;

        

    }

    
    public function processProduct($ean_code,$invoice_in_id){
        
        $eanExist = $this->db->table('ci_product_eans')
            ->where('ean', $ean_code)
            ->get()
            ->getRow();

            if(!$eanExist){
                return ['error' => true, 'ean_status' => 'EAN-ul inserat nu exista in baza de date, te rog sa il adaugi produsului corespondent'];
            }

            $ProductsModel = new ProductsModel();
            $product_name = $ProductsModel->findProductNamebyId($eanExist->product_id);

            $stock_row = $this->db->table('stock_copy1')
                ->where('product_id', $eanExist->product_id)
                ->where('invoice_in_id', $invoice_in_id)
                ->where('warehouse', 2)
                ->where('reception_date IS NULL', null, false)
                ->get()
                ->getRow();

                if(!$stock_row){
                    return ["error" => true, 'ean_exist' => 0, 'message' => "This ean ".$ean_code." is not allocated for any product on invoice ".$invoice_in_id.""];
                }

            if ($eanExist){

                $countBeforeAdd = $this->db->table('stock_copy1')
                    ->where('product_id', $eanExist->product_id)
                    ->where('warehouse', 2)
                    ->where('reception_date IS NULL', null, false)
                    ->countAllResults();

                if($countBeforeAdd <= 0){

                    return ['row_id' => $stock_row->id, 'already_receptioned' => 1, 'message' => 'This product was already marked as receptioned'];

                }
            
                $insert_data_stock = [
                    'ean' => $ean_code,
                    'reception_date' => date('Y-m-d H:i:s'),
                    'warehouse' => '1'
                ];

                $this->db->table('stock_copy1')
                ->set($insert_data_stock)
                ->where('id', $stock_row->id)
                ->update();


                $count = $this->db->table('stock_copy1')
                    ->where('product_id', $eanExist->product_id)
                    ->where('warehouse', 2)
                    ->where('invoice_in_id', $invoice_in_id)
                    ->where('reception_date IS NULL', null, false)
                    ->countAllResults();
                
                $return = ['row_id' => $stock_row->id, 'ean_exist' => 1, 'message' => 'Product succesfully marked as receptioned', 'remains_to_be_receptioned' => $count];
               
                return $return;

            }

            }

            
    
    /* public function processProduct($product_id, $ean_code, $row_id, $ean_exist)
    {    

        $checkifeanExist = $this->db->table('ci_product_eans')
                ->where('ean', $ean_code)
                ->where("product_id != ", $product_id)
                ->get();

        // If the EAN exists for another product
        if ($checkifeanExist->getNumRows() > 0) {

            $eanrows = $checkifeanExist->getRow();

            $ProductsModel = new ProductsModel();
            $product_name = $ProductsModel->findProductNamebyId($product_id);
            

            return ['error' => true, 'ean_linked_with_other_product' => 1, 'product_id' => $eanrows->product_id, 'product_name' => $product_name];
        }
        
        
        $stock_row = $this->db->table('stock_copy1')
        ->where('id', $row_id)
        ->get()
        ->getRow();
        
        if($stock_row->reception_date !== null) {
        return ['row_id' => $row_id, 'already_receptioned' => 1, 'message' => 'This product was already marked as receptioned'];
        }
       
        $query = $this->db->table('ci_product_eans')
            ->where('product_id', $product_id)
            ->get();

        $eans = $query->getResult();

        $ean_found = false;
        foreach($eans as $ean){
            if($ean->ean == $ean_code){
                $ean_found = true;
                break;
            }
        }

        // If the EAN code is found in the database for the product, or ean_exist is 1
        if ($ean_found || $ean_exist == 1)
        {
            
            
            $insert_data_stock = [
                'ean' => $ean_code,
                'reception_date' => date('Y-m-d H:i:s'),
                'warehouse' => '1'
            ];

            $this->db->table('stock_copy1')
                ->set($insert_data_stock)
                ->where('id', $row_id)
                ->update();

            
            if ($ean_exist == 1 && !$ean_found)
            {
                $this->db->table('ci_product_eans')
                    ->insert(['product_id' => $product_id, 'ean' => $ean_code]);
            }
            
            $return = ['row_id' => $row_id, 'ean_exist' => 1, 'message' => 'Product succesfully marked as receptioned'];
            return $return;
        }
        else
        {
            $return = ['row_id' => $row_id, 'ean_exist' => 0, 'message' => 'This ean does not exist, retry the call with publish_ean 1'];
            return $return;
        }
    }*/

}