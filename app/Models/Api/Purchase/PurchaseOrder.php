<?php

namespace App\Models\Api\Purchase\PurchaseOrder;

use CodeIgniter\Model;

class PurchaseOrder extends Model
{

    protected $table = 'invoices_in';
    protected $primaryKey = 'id';


    // Returning order list (including total with vat and without)
    // Used in Orders Controller for Endpoint "getAll"
    public function getInfo()
    {


        $WarehouseModel = new \App\Models\Api\Inventory\WarehouseModel();
        $SupplierModel = new \App\Models\Api\Suppliers\SupplierModel();



        $warehouses = $WarehouseModel->getWarehouseList(); // Fetching warehouse list from database
        $suppliers = $SupplierModel->getSuppliersList(); // Fetching suppliers list from database

        
        $prepareArray = [
            "suppliers" => $suppliers,
            "warehouses" => $warehouses
        ];

        return $prepareArray;
    }

    public function getPurchaseList(){

        $this->select('
        invoices_in.id,
        invoices_in.invoice_date,
        invoices_in.due_date,
        invoices_in.supplier_id,
        invoices_in.invoice_series,
        invoices_in.number AS invoice_number,
        suppliers.name AS supplier_name,
        suppliers.code AS supplier_code,
        transport.driver,
        transport.number AS nr_auto,
        invoices_in.image,
        invoices_in.locked,
        invoices_in.currency_rate,
        invoices_in.invoice_value,
        invoices_in.currency,
        invoices_in.reception_date'
    );

        $this->join('suppliers', 'suppliers.id = invoices_in.supplier_id', 'left');
        $this->join('transport', 'transport.id = invoices_in.transport', 'left');
        $this->orderBy('invoices_in.reception_date', 'DESC');
        $query = $this->get();
        $purchases = $query->getResultArray();

        $PurchaseOrderProduct = new \App\Models\Api\Purchase\PurchaseOrderProduct();

        foreach ($purchases as &$purchase) {

            $getPurchaseOrderValueWithVat = $PurchaseOrderProduct->getPurchaseOrderValueWithVat($purchase['id'],$purchase['currency_rate']);
            $getPurchaseOrderValueNoVat = $PurchaseOrderProduct->getPurchaseOrderValueNoVat($purchase['id'],$purchase['currency_rate']);

            $order['totalNoVat'] = round($getPurchaseOrderValueNoVat, 4);
            $order['totalWithVat'] = round($getPurchaseOrderValueWithVat, 4);
        }

        return $purchases;
    }

}