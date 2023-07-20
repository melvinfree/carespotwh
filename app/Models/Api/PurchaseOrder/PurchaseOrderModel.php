<?php

namespace App\Models\Api\PurchaseOrder;

use CodeIgniter\Model;

class PurchaseOrderModel extends Model
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

    public function getPurchaseList($limit, $offset){

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
        $this->limit($limit, $offset);
        $query = $this->get();
        $purchases = $query->getResultArray();

        $PurchaseOrderProduct = new \App\Models\Api\PurchaseOrder\PurchaseOrderProductModel();

        foreach ($purchases as &$purchase) {

            $getPurchaseOrderValueWithVat = $PurchaseOrderProduct->getPurchaseOrderValueWithVat($purchase['id'],$purchase['currency_rate']);
            $getPurchaseOrderValueNoVat = $PurchaseOrderProduct->getPurchaseOrderValueNoVat($purchase['id'],$purchase['currency_rate']);

            $purchase['totalNoVat'] = round($getPurchaseOrderValueNoVat, 4);
            $purchase['totalWithVat'] = round($getPurchaseOrderValueWithVat, 4);
        }

        return $purchases;
    }

    public function createPurchase($data)
    {
        $this->db->table($this->table)->insert($data);
        return $this->db->insertID(); // returns the ID of the inserted record
    }

    public function modifyInvoice($requestData){
        
        $invoice = $this->find($requestData['invoice_id']);

        if($invoice === null) {
            // The invoice does not exist
            return ['invoice_id' => $requestData['invoice_id'], 'status' => 'missing'];

        }

        if ($invoice['locked'] == 1) {
            return ['status' => 'locked', 'invoice_id' => $requestData['invoice_id']];
        }

        
        
        if (isset($requestData['invoice_number'])){

            $this->set('number', $requestData['invoice_number'])
                 ->where('invoice_id', $requestData['invoice_id'])
                 ->update();

             return ['invoice_id' => $requestData['invoice_id'], 'status' => 'succes'];

        }

        if (isset($requestData['invoice_series'])){

            $this->set('number', $requestData['invoice_series'])
                 ->where('invoice_id', $requestData['invoice_id'])
                 ->update();

             return ['invoice_id' => $requestData['invoice_id'], 'status' => 'succes'];

        }

        if (isset($requestData['invoice_date'])){

            $this->set('number', $requestData['invoice_date'])
                 ->where('invoice_id', $requestData['invoice_id'])
                 ->update();

             return ['invoice_id' => $requestData['invoice_id'], 'status' => 'succes'];

        }



    }

}