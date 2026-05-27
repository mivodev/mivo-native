<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;

    private $pdo;

    private function __construct()
    {
        $dbPath = ROOT.'/app/Database/database.sqlite';
        try {
            $this->pdo = new PDO('sqlite:'.$dbPath);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            exit('Database Connection Failed: '.$e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function getConnection()
    {
        return $this->pdo;
    }

    // Helper to run query with params
    public function query($sql, $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }
}
