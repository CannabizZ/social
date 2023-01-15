<?php
declare(strict_types=1);

namespace App\Base;

use PDO;

class DialogsDbConnect extends PDO
{
    /** @var array[] */
    private array $connections = [];
    private array|null $options = null;

    public function __construct()
    {
        parent::__construct(
            'mysql:host=social-proxysql:6033;dbname=dialogs',
            'test',
            'pzjqUkMnc7vfNHET',
            $this->options
        );
    }
}