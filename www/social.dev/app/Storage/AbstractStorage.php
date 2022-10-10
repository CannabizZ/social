<?php
declare(strict_types=1);

namespace App\Storage;

use App\Base\DbConnect;
use App\Exception\RuntimeException;
use PDOStatement;


abstract class AbstractStorage
{
    protected DbConnect $db;

    public function __construct()
    {
        $this->db = new DbConnect();
    }

    /**
     * @param string $query
     * @return PDOStatement
     * @throws RuntimeException
     */
    protected function query(string $query): PDOStatement
    {
        $result = $this->db->query($query);
        if ($result === false) {
            throw new RuntimeException(
                "PDO error code: " . $this->db->errorCode() .
                "\n info: " . var_export($this->db->errorInfo(), true)
            );
        }

        return $result;
    }
}