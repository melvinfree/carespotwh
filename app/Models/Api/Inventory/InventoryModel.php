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
    invoices_in.invoice_value,
    invoices_in.currency,
    COUNT(stock_copy1.id) as total_quantity,
    SUM(IF(stock.reception_date IS NOT NULL, 1, 0)) as receptioned_quantity');

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
    $this->groupBy("invoices_in.id");
    $this->orderBy("invoices_in.id", "DESC");
    $query = $this->get();
    $receptions = $query->getResultArray();

    foreach ($receptions as &$reception) {
        $reception['not_receptioned_quantity'] = $reception['total_quantity'] - $reception['receptioned_quantity'];
    }

    return $receptions;
}

}