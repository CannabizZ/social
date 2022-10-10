<?php
declare(strict_types=1);

namespace App\Exception;

use Exception;

class RouteException extends Exception
{
    protected $message = 'Route exception';
}