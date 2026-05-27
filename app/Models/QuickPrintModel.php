<?php

namespace App\Models;

use App\Core\Database;

class QuickPrintModel
{
    public function getAllByRouterId($routerId)
    {
        $db = Database::getInstance();
        $stmt = $db->query('SELECT * FROM quick_prints WHERE router_id = ?', [$routerId]);

        return $stmt->fetchAll();
    }

    public function getById($id)
    {
        $db = Database::getInstance();
        $stmt = $db->query('SELECT * FROM quick_prints WHERE id = ?', [$id]);

        return $stmt->fetch();
    }

    public function add($data)
    {
        $db = Database::getInstance();
        // Insert router_id. session_name is kept for legacy/redundancy if needed, or we can drop it.
        // Let's write both for now to be safe during transition, or user requirement "diubah saja" implies replacement using ID.
        // But the table still has session_name column (we added router_id, didn't drop session_name).
        $sql = 'INSERT INTO quick_prints (router_id, session_name, name, server, profile, prefix, char_length, price, selling_price, time_limit, data_limit, comment, color) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

        return $db->query($sql, [
            $data['router_id'],
            $data['session_name'], // Keep filling it for now
            $data['name'],
            $data['server'] ?? 'all',
            $data['profile'],
            $data['prefix'] ?? '',
            $data['char_length'] ?? 4,
            $data['price'] ?? 0,
            $data['selling_price'] ?? ($data['price'] ?? 0),
            $data['time_limit'] ?? '',
            $data['data_limit'] ?? '',
            $data['comment'] ?? '',
            $data['color'] ?? 'bg-blue-500',
        ]);
    }

    public function update($id, $data)
    {
        $db = Database::getInstance();
        $sql = 'UPDATE quick_prints SET name=?, profile=?, prefix=?, char_length=?, price=?, selling_price=?, time_limit=?, data_limit=?, comment=?, color=?, updated_at=CURRENT_TIMESTAMP WHERE id=?';

        return $db->query($sql, [
            $data['name'],
            $data['profile'],
            $data['prefix'] ?? '',
            $data['char_length'] ?? 4,
            $data['price'] ?? 0,
            $data['selling_price'] ?? ($data['price'] ?? 0),
            $data['time_limit'] ?? '',
            $data['data_limit'] ?? '',
            $data['comment'] ?? '',
            $data['color'] ?? 'bg-blue-500',
            $id,
        ]);
    }

    public function delete($id)
    {
        $db = Database::getInstance();

        return $db->query('DELETE FROM quick_prints WHERE id = ?', [$id]);
    }
}
