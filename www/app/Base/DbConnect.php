<?php
declare(strict_types=1);

namespace App\Base;

use PDO;

class DbConnect extends PDO
{
    /** @var array[] */
    private array $connections = [];

    public const CONN_MASTER = 'master';
    public const CONN_SLAVE = 'slave';

    private array|null $options = null;

    public function __construct()
    {
        $this->initConfig();
        $connection = $this->getConnection(self::CONN_MASTER);
        parent::__construct(
            'mysql:host=' . $connection['host'] . ';dbname=social',
            $connection['user'],
            $connection['password'],
            $this->options
        );
    }

    /**
     * @param string $key
     * @return string[]
     */
    private function getConnection(string $key): array
    {
        $count = count($this->connections[$key]) - 1;
        return $this->connections[$key][rand(0,$count)];
    }

    /**
     * @return void
     */
    private function initConfig(): void
    {
        $user = getenv('DB_USER');
        $password = getenv('DB_PASSWORD');

        $masterHosts = getenv('DB_MASTER_HOSTS');
        if ($masterHosts) {
            foreach (explode(',', $masterHosts) as $host) {
                $this->connections[self::CONN_MASTER][] = [
                    'host' => $host,
                    'user' => $user,
                    'password' => $password
                ];
            }
        }

        $slaveHosts = getenv('DB_SLAVE_HOSTS');
        if ($slaveHosts) {
            foreach (explode(',', $slaveHosts) as $host) {
                $this->connections[self::CONN_SLAVE][] = [
                    'host' => $host,
                    'user' => $user,
                    'password' => $password
                ];
            }
        }
    }
}