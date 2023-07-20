<?php

namespace App\Models\Api\PurchaseOrder;

use CodeIgniter\Model;

class PurchaseOrderProductModel extends Model
{

    protected $allowedFields = [
        'invoice_id'
    ];

    protected $table = 'invoices_in_products';
    protected $primaryKey = 'id';

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
    
    public function addInvoiceProductsToStock($invoiceId, $warehouseId)
    {
        $invoiceProducts = $this->where('invoice_id', $invoiceId)->findAll();

        $stockModel = new \App\Models\Api\Inventory\StockModel();
        $purchaseOrderModel = new \App\Models\Api\PurchaseOrder\PurchaseOrderModel();

        $invoice = $purchaseOrderModel->where('id', $invoiceId)->first();
        $supplierId = $invoice['supplier_id'];
        $currency_rate = $invoice['currency_rate'];



        foreach ($invoiceProducts as $product) {

            

            $data = [
                'product_id' => $product['product_id'],
                'supplier' => $supplierId,
                'quantity' => $product['quantity'],
                'invoice_in_id' => $product['invoice_id'],
                'invoice_product_id' => $product['id'],
                'warehouse' => $warehouseId,
                'discount' => $product['discount'],   // need to add discount to production database table (invoices_in_products)
                'status' => 'instock',
                'acquisition_price' => $product['acquisition_price'] * $currency_rate,
                'acquisition_price_invoice' => $product['acquisition_price'] * $currency_rate,
            ];



            $stockModel->addToStock($data);
        }

        return "success";
    }

    
    
    // Need to improve conditions to fit better (it works perfect in tests)
    public function updateInvoiceId($currentInvoiceId, $newInvoiceId, $rowId)
    {
        // Get all products associated with the current invoice from the stock table
        $stockModel = new \App\Models\Api\Inventory\StockModel();
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


            if ($product['status'] != 'instock' && $product['status'] = 'allocated'  && $product['order_id'] == null) {
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
    
        return 'Produsul '.$pData['product_name'].' a fost mutat in nir-ul '. $newInvoice['id'] .'';
    }




    
    public function deleteProduct($rowId,$InvoiceId)
    {
        // Get all products associated with the current invoice from the stock table
        $stockModel = new \App\Models\Api\Inventory\StockModel();
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


            if ($product['status'] != 'instock' && $product['status'] = 'allocated'  && $product['order_id'] == null) {
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
    
        return 'Produsul '.$row['product_name'].' a fost sters din nir-ul '. $InvoiceId .'';
    }

}

