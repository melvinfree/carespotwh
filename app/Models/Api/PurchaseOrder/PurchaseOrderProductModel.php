<?php

namespace App\Models\Api\PurchaseOrder;

use CodeIgniter\Model;

class PurchaseOrderProductModel extends Model
{

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

    }

