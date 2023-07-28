<?php

namespace App\Models\Api\Inventory;

use CodeIgniter\Model;

class InventoryModel extends Model
{

    protected $table = 'invoices_in'; // need to change the table here.
    protected $primaryKey = 'id';


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

}