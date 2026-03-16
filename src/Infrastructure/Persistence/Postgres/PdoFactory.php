<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Postgres;

final class PdoFactory
{
    public static function create(): \PDO
    {
        $host = 'postgres';
        $port = 5432;
        $dbName = 'orders';
        $user = 'orders_user';
        $password = 'orders_password';

        $dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s', $host, $port, $dbName);

        $pdo = new \PDO($dsn, $user, $password, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ]);

        return $pdo;
    }
}