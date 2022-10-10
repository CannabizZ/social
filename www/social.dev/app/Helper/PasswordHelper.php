<?php
declare(strict_types=1);

namespace App\Helper;

class PasswordHelper
{
    /**
     * @param string $password
     * @return string
     */
    public static function getHash(string $password): string
    {
        return password_hash($password,  PASSWORD_DEFAULT);
    }

    /**
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public static function check(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}