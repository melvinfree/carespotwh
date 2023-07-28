<?php

namespace App\Models\Api\Inventory;

use CodeIgniter\Model;

class Receptions extends Model
{

    protected $table = 'invoices_in'; // need to change the table here.
    protected $primaryKey = 'id';


    // This function it will be used to display receptions list inside Inventory Controller.
    // This function it will return all receptions which wasn't confirmed(receptioned in warehouse) and which are locked(nir was closed)
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
            SUM(IF(stock_copy1.reception_date IS NOT NULL, 1, 0)) as receptioned_quantity
        FROM 
            invoices_in_products
        JOIN 
            invoices_in ON invoices_in.id = invoices_in_products.invoice_id
        JOIN
            stock_copy1 ON stock_copy1.invoice_product_id = invoices_in_products.id    
        WHERE 
            invoices_in_products.invoice_id = ?", [$invoice_id]);

    return $query->getResult();
    }

}