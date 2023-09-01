<?php

namespace App\Helpers;

use CodeIgniter\Model;
use CodeIgniter\Database\ConnectionInterface;

class StockHelper
{
    private $db;

    public function __construct(ConnectionInterface &$db)
    {
        $this->db = &$db;
    }

    public function markProductAsPicked($table, $productId, $condition, $message): array
    {
        $stockRow = $this->db->table($table)
            ->where($condition)
            ->where('picked', 0)
            ->get()
            ->getRow();

        if (!$stockRow) {
            return ['already_picked' => 1, 'message' => 'This product was already marked as picked'];
        }

        $updateDataStock = [
            'picked' => 1,
        ];

        $this->db->table($table)
            ->set($updateDataStock)
            ->where('id', $stockRow->id)
            ->update();

        $count = $this->db->table($table)
            ->where($condition)
            ->where('picked', 0)
            ->countAllResults();

        return ['row_id' => $stockRow->id, 'ean_exist' => 1, 'message' => $message, 'remains_to_be_picked' => $count];
    }
}
