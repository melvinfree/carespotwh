<?php

namespace App\Models\Api\PurchaseOrder;

use CodeIgniter\Model;
use App\Models\Api\Inventory\ProductsModel;
use App\Models\Api\Inventory\StockModel;

class PurchaseOrderProductModel extends Model
{

    protected $allowedFields = [
        'invoice_id'
    ];

    protected $table = 'invoices_in_products';
    protected $stockTable = 'stock_copy1';
    protected $primaryKey = 'id';

    public function get_invoice_products($invoice_id)
    {

        $query = $this->db->query("
        SELECT 
            invoices_in_products.id AS row_id,
            invoices_in_products.product_id,
            invoices_in_products.product_name,
            invoices_in.warehouse_name,
            invoices_in_products.tax,
            invoices_in_products.discount,
            invoices_in_products.quantity,
            ROUND(invoices_in_products.acquisition_price * invoices_in.currency_rate,4) as price_ron,
            invoices_in_products.acquisition_price,
            invoices_in.currency,
            ROUND(invoices_in_products.acquisition_price * invoices_in.currency_rate * invoices_in_products.quantity, 4) as total_no_vat
        FROM 
            invoices_in_products
        JOIN 
            invoices_in ON invoices_in.id = invoices_in_products.invoice_id
        WHERE 
            invoices_in_products.invoice_id = ?", [$invoice_id]);

    return $query->getResult();

    }
    

    //USED FOR REVERSED INVOICES ( TO SHOW A PRODUCTS LIST)
    public function get_products_for_reversal($invoice_id)
    {

        $query = $this->db->query("
        SELECT 
            invoices_in_products.id AS row_id,
            invoices_in_products.product_id,
            invoices_in_products.product_name,
            invoices_in.warehouse_name,
            invoices_in_products.tax,
            invoices_in_products.discount,
            invoices_in_products.quantity,
            ROUND(invoices_in_products.acquisition_price * invoices_in.currency_rate,4) as price_ron,
            invoices_in_products.acquisition_price,
            invoices_in.currency,
            ROUND(invoices_in_products.acquisition_price * invoices_in.currency_rate * invoices_in_products.quantity, 4) as total_no_vat
        FROM 
            invoices_in_products
        JOIN 
            invoices_in ON invoices_in.id = invoices_in_products.invoice_id
        WHERE 
        invoices_in_products.invoice_id = ?", [$invoice_id]);

    return $query->getResult();
    
    }

    public function reverseInvoiceProductStock($data){

        $stockModel = new StockModel();
        $purchaseOrderModel = new \App\Models\Api\PurchaseOrder\PurchaseOrderModel();
        $ProductsModel = new ProductsModel();

        $initial_invoice_id = $purchaseOrderModel->find($data['storno_for']);

        foreach ($data['products'] as $reversalProduct){

            $old_product_line =  $this->db->table($this->table)
                                 ->where('invoice_id', $initial_invoice_id['id'])
                                 ->where('product_id', $reversalProduct['product_id'])
                                 ->get()
                                 ->getRow();
            
            
            $dbRecord = $this->db->table($this->table)
                                 ->where('invoice_id', $data['invoice_id'])
                                 ->where('product_id', $reversalProduct['product_id'])
                                 ->get()
                                 ->getRow();


            if($dbRecord) {

                $updateData = [
                    'quantity' => "-".$reversalProduct['quantity'],
                ];
    
                $this->db->table($this->table)
                    ->where('invoice_id', $data['invoice_id'])
                    ->where('product_id', $reversalProduct['product_id'])
                    ->where('id', $dbRecord->id)
                    ->update($updateData);

                    $insertId = $dbRecord->id;

            } 
            else{


            $products = [
                'invoice_id' => $data['invoice_id'],
                'product_id' => $reversalProduct['product_id'],
                'product_name' => $ProductsModel->findProductNamebyId($reversalProduct['product_id']),
                'acquisition_price' => $old_product_line->acquisition_price,
                'quantity' => "-".$reversalProduct['quantity'],
                'tax' => $old_product_line->tax
            ];
            
            $this->db->table($this->table)->insert($products);
            $insertId = $this->db->insertID();

            $responses[] = ['record_id' => $insertId];

        }

        // Start operations for stock.

        $updateData = [
            'order_id' => NULL,
            'invoice_out_id' => NULL,
            'invoice_in_storno_id' => $data['invoice_id'],
            'invoice_in_product_storno_id' => $insertId,
            'status' => 'reversesale_supplier'
        ];

        $count_already_reversed_product = $stockModel->where('invoice_in_storno_id', $data['invoice_id'])
                                                ->where('invoice_in_product_storno_id', $insertId)
                                                ->where('status', 'reversesale_supplier')
                                                ->get()    
                                                ->getResultArray();

        return ['cant' => count($count_already_reversed_product) - $reversalProduct['quantity']];
        // WHAT HAPPEND IF Already reversed products from table stock is lower than quantity which should be reversed
       
        if(count($count_already_reversed_product) < $reversalProduct['quantity']){

            $limit = $reversalProduct['quantity'] - $count_already_reversed_product;
        
            
            $ProductsToBeReversed = $stockModel->where('invoice_in_id', $initial_invoice_id['id'])
                                                ->where('product_id', $reversalProduct['product_id'])
                                                ->where('status', 'instock')
                                                ->orWhere('status', 'allocated_service')
                                                ->limit($limit)
                                                ->get()    
                                                ->getResultArray();

            if(count($ProductsToBeReversed) < $reversalProduct['quantity'])  {
                return ["error" => true, "message" => "You cannot reversed maximum products count($ProductsToBeReversed) pcs for this product"];
            }                                  
                                                
            

            foreach ($ProductsToBeReversed as $stock_line){
                
                $updateData = [
                    'order_id' => NULL,
					'invoice_out_id' => NULL,
                    'invoice_in_storno_id' => $data['invoice_id'],
                    'invoice_in_product_storno_id' => $insertId,
					'status' => 'reversesale_supplier'
                ];

                $this->db->table($this->stockTable)
                     ->where('invoice_in_id', $initial_invoice_id['id'])
                     ->where('id', $stock_line['id'])
                     ->update($updateData);

            }

        }

        elseif(count($count_already_reversed_product) > $reversalProduct['quantity'])

        // need to stable the limit for prods to be put back in stock.

       // $limit = count($count_already_reversed_product) - $reversalProduct['quantity'];

        

        $ProductsToBeReversed = $stockModel->where('invoice_in_id', $initial_invoice_id['id'])
                                                ->where('invoice_in_storno_id' , $data['invoice_id'])
                                                ->where('invoice_in_product_storno_id', $insertId)
                                                ->where('product_id', $reversalProduct['product_id'])
                                                ->where('status', 'reversesale_supplier')
                                                ->limit($limit)
                                                ->get()    
                                                ->getResultArray();                                
                                                
                        

            foreach ($ProductsToBeReversed as $stock_line){
                
                $updateData = [
                    'order_id' => NULL,
					'invoice_out_id' => NULL,
                    'invoice_in_storno_id' => NULL,
                    'invoice_in_product_storno_id' => NULL,
					'status' => 'instock'
                ];

                $this->db->table($this->stockTable)
                     ->where('invoice_in_id', $initial_invoice_id['id'])
                     ->where('id', $stock_line['id'])
                     ->update($updateData);

            }



        }

        return ["error" => false];
        

    }


    public function getPurchaseOrderValueNoVat($purchaseOrderId,$currency_rate){

        $query = $this->selectSum('(quantity * acquisition_price * ' . $currency_rate . ')', 'total_without_vat')
            ->where('invoice_id', $purchaseOrderId)
            ->get();

        if ($query->getNumRows() > 0) {
            $row = $query->getRow();
            return $row->total_without_vat;
        } else {
            return 0; // Return 0 if no rows found for the given invoice_id
        }
    }

    public function getPurchaseOrderValueWithVat($purchaseOrderId,$currency_rate){

        $query = $this->selectSum('(quantity * acquisition_price * tax * ' . $currency_rate . ')', 'total_with_vat')
            ->where('invoice_id', $purchaseOrderId)
            ->get();

        if ($query->getNumRows() > 0) {
            $row = $query->getRow();
            return $row->total_with_vat;
        } else {
            return 0; // Return 0 if no rows found for the given invoice_id
        }
    }

    // Adaugare produse pe nir
    // Daca produsul deja exista se actualizeaza valorile / daca nu, se adauga linie noua.
    public function insertProducts($data)
    {
    $responses = [];

    $invoice_id = $data['invoice_id'];

    foreach($data['products'] as $product) {
        $dbRecord = $this->db->table($this->table)
             ->where('invoice_id', $invoice_id)
             ->where('product_id', $product['product_id'])
             ->get()
             ->getRow();


        if($dbRecord) {

            $products = [
                'acquisition_price' => $product['acquisition_price'],
                'quantity' => $product['quantity'],
                'tax' => $product['tax']
            ];

            // Perform update operation and add the result to $responses
            $this->db->table($this->table)
                ->where('invoice_id', $invoice_id)
                ->where('product_id', $product['product_id'])
                ->update($products);

                if ($this->db->affectedRows() > 0) {
                    $updatedRecord = $this->db->table($this->table)
                                       ->where('invoice_id', $invoice_id)
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
                'invoice_id' => $invoice_id,
                'product_id' => $product['product_id'],
                'product_name' => $product['product_name'],
                'acquisition_price' => $product['acquisition_price'],
                'quantity' => $product['quantity'],
                'tax' => $product['tax']
            ];
            
            $this->db->table($this->table)->insert($products);
            $responses[] = ['record_id' => $this->db->insertID()];
        }
    }

    return $responses;
    }

    //Function description
    //This function is used to fetch data from invoices_in & invoices_in_products and then those data are pushed to stock table
    //as a unit lines (e.g. if was ordered 30 pcs of a product, in stock it will go 30 different rows)
    
    public function addInvoiceProductsToStock($invoiceId)
    {
        $invoiceProducts = $this->where('invoice_id', $invoiceId)->findAll();

        $stockModel = new StockModel();
        $purchaseOrderModel = new \App\Models\Api\PurchaseOrder\PurchaseOrderModel();

        $invoice = $purchaseOrderModel->where('id', $invoiceId)->first();
        $supplierId = $invoice['supplier_id'];
        $currency_rate = $invoice['currency_rate'];
        $warehouse_id = $invoice['warehouse_id'];



        foreach ($invoiceProducts as $product) {

            
            $data = [
                'product_id' => $product['product_id'],
                'supplier' => $supplierId,
                'quantity' => $product['quantity'],
                'invoice_in_id' => $product['invoice_id'],
                'invoice_product_id' => $product['id'],
                'warehouse' => $warehouse_id,
                'discount' => $product['discount'],   // need to add discount to production database table (invoices_in_products)
                'status' => 'instock',
                'acquisition_price' => $product['acquisition_price'] * $currency_rate,
                'acquisition_price_invoice' => $product['acquisition_price'] * $currency_rate,
            ];



            $stockModel->addToStock($data);

        }

        return ["error"=> false, "message" => "success"];
    }

    
    
    // Need to improve conditions to fit better (it works perfect in tests)
    public function updateInvoiceId($currentInvoiceId, $newInvoiceId, $rowId)
    {
        // Get all products associated with the current invoice from the stock table
        $stockModel = new StockModel();
        $purchaseOrderModel = new \App\Models\Api\PurchaseOrder\PurchaseOrderModel(); 

        $row = $this->find($rowId);
        
        $productId = $row['product_id'];
        
        $products = $stockModel->where('invoice_in_id', $currentInvoiceId)
                           ->where('product_id', $productId)
                           ->findAll();
    
        // Prepare an array to hold order ids of products that are not 'instock'
        $nonInstockOrders = [];
    
        // Check if any product has a status other than 'instock'
        foreach ($products as $product) {

        $pData = $this->find($rowId);


            if ($product['status'] != 'instock' && $product['status'] == 'allocated'  && $product['order_id'] == null) {
                // One of the products is not 'instock', store its order_id in the array
                $nonInstockOrders[] = ["order_id" => $product['order_id'],
                                       "product_name" => $pData['product_name'],
                                    ];
            }
        }
    
        // If there are any products not 'instock', return the error messages
        if (!empty($nonInstockOrders)) {
            return $nonInstockOrders;
        }

        // Check if the new invoice is locked
        $newInvoice = $purchaseOrderModel->find($newInvoiceId);
        if ($newInvoice['locked'] == 1) {
            return 'Nir-ul este inchis '. $newInvoice['id'] .'. Nu poti muta produsul in acel nir.';
        }

        // All products are 'instock' or 'allocated', and the new invoice is not locked. So, update the invoice_id in the invoices_in_products table for the specific product.
        $this->set('invoice_id', $newInvoiceId)
             ->where('invoice_id', $currentInvoiceId)
             ->where('product_id', $productId)
             ->update();

        // Also update the invoice_in_id in the stock table for the specific product
        $stockModel->set('invoice_in_id', $newInvoiceId)
                   ->where('invoice_in_id', $currentInvoiceId)
                   ->where('product_id', $productId)
                   ->update();
    
                   $returnArr = ["product_id" => $row['product_id'], "status" => 'moved', 'invoice_id' => $currentInvoiceId, 'new_invoice_id' => $newInvoiceId];
    
                   return $returnArr;
    }




    
    public function deleteProduct($rowId,$InvoiceId)
    {
        // Get all products associated with the current invoice from the stock table
        $stockModel = new StockModel();
        $purchaseOrderModel = new \App\Models\Api\PurchaseOrder\PurchaseOrderModel(); 

        $row = $this->find($rowId);

        $productId = $row['product_id'];
        
        $products = $stockModel->where('invoice_in_id', $InvoiceId)
                               ->where('product_id', $productId)
                               ->findAll();
    
        // Prepare an array to hold order ids of products that are not 'instock'
        $nonInstockOrders = [];
    
        // Check if any product has a status other than 'instock'
        foreach ($products as $product) {

        $pData = $this->find($rowId);


            if ($product['status'] != 'instock' && $product['status'] == 'allocated'  && $product['order_id'] == null) {
                $nonInstockOrders[] = ["order_id" => $product['order_id'],
                                       "product_name" => $pData['product_name'],
                                    ];
            }
        }
    
        // If there are any products not 'instock', return the error messages
        if (!empty($nonInstockOrders)) {
            return $nonInstockOrders;
        }

        // Delete product from current NIR.
        $this->delete(['id' => $rowId]);

        // Also delete product records from stock table.
        $stockModel->where('invoice_in_id', $InvoiceId)
                     ->delete();

                     $returnArr = ["product_id" => $row['product_id'], "status" => 'deleted', 'invoice_id' => $InvoiceId];
    
        return $returnArr;
    }

}

