<?php
declare(strict_types=1);

namespace App\Storage;

use App\Base\DialogsDbConnect;
use App\Exception\RuntimeException;
use PDO;

class DialogsStorage
{
    protected DialogsDbConnect $db;

    public function __construct()
    {
        $this->db = new DialogsDbConnect();
    }

    /**
     * @param int $userId
     * @param int $recipientId
     * @param string $message
     * @return void
     * @throws RuntimeException
     */
    public function add(int $userId, int $recipientId, string $message): void
    {
        $sql = "
                INSERT INTO history (shardId, userId, recipientId, message) VALUES (" . $this->getShardId($userId, $recipientId) . ", $userId,
                        $recipientId,
                        " . $this->db->quote($message) . "
                    )
            ";

        $result = $this->db->query($sql);
        if ($result === false) {
            throw new RuntimeException(
                "PDO error code: " . $this->db->errorCode() .
                "\n info: " . var_export($this->db->errorInfo(), true)
            );
        }
    }

    /**
     * @param int $userId
     * @param int $recipientId
     * @return array
     * @throws RuntimeException
     */
    public function get(int $userId, int $recipientId): array
    {
        $sql = "
                SELECT userId, recipientId, message, created FROM  history 
                WHERE 
                    shardId = " . $this->getShardId($userId, $recipientId) . " AND
                    (
                        userId = " . $userId . " AND
                        recipientId = " . $recipientId . "
                    ) OR
                    (
                        recipientId = " . $userId . " AND
                        userId = " . $recipientId . "
                    )
                ORDER BY created DESC
            ";

        $result = $this->db->query($sql);
        if ($result === false) {
            throw new RuntimeException(
                "PDO error code: " . $this->db->errorCode() .
                "\n info: " . var_export($this->db->errorInfo(), true)
            );
        }

        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function getShardId(int $userId, int $recipientId): int
    {
        return ($userId + $recipientId) % 2;
    }
}