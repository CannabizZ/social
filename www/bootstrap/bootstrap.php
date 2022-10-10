<?php

use App\Base\Router;

ob_start();

set_error_handler(function (int $errno, string $errstr, ?string $errfile, ?int $errline) {
    $exception = new \App\Exception\RuntimeException($errstr, $errno);

    $errfile !== null && $exception->setFile($errfile);
    $errline !== null && $exception->setLine($errline);

    header('Content-Type: application/json; charset=utf-8');
    echo Router::prepareError($exception);
    exit();
}, E_ALL);

set_exception_handler(function (Throwable $throwable) {
    header('Content-Type: application/json; charset=utf-8');
    echo Router::prepareError($throwable);
    exit();
});