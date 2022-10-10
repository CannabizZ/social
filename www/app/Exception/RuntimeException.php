<?php
declare(strict_types=1);

namespace App\Exception;

use Exception;

class RuntimeException extends Exception
{
    protected $message = 'Runtime exception';

    /**
     * @param string $file
     * @return void
     */
    public function setFile(string $file): void
    {
        $this->file = $file;
    }

    /**
     * @param int $line
     * @return void
     */
    public function setLine(int $line): void
    {
        $this->line = $line;
    }
}