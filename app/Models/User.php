<?php

namespace App\Models;

use App\Core\Database;

class User
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function attempt($username, $password)
    {
        $stmt = $this->db->query('SELECT * FROM users WHERE username = ?', [$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }
}
