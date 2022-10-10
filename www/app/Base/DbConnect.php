<?php
declare(strict_types=1);

namespace App\Base;

use PDO;

class DbConnect extends PDO
{
    private string $dsn = 'mysql:host=mysql;dbname=social';
    private string $userName = 'root';
    private string $password = 'secret';
    private array|null $options = null;

    public function __construct()
    {
        parent::__construct($this->dsn, $this->userName, $this->password, $this->options);
    }
}