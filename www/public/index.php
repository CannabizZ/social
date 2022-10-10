<?php

use App\Base\Request;
use App\Base\Response;
use App\Base\Router;

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__ . '/../bootstrap/bootstrap.php';
require_once __DIR__ . '/../config/routes.php';


$response = Router::prepare(new Request(), new Response());

header('Content-Type: application/json; charset=utf-8');
echo $response;