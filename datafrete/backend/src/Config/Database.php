<?php

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private $connection;

    private function __construct()
    {
        $this->loadEnv();
        $this->connect();
    }

    private function loadEnv()
    {
        $envFile = __DIR__ . '/../../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }
                list($name, $value) = explode('=', $line, 2);
                $_ENV[trim($name)] = trim($value);
            }
        }
    }

    private function connect()
    {
        $host = isset($_ENV['DB_HOST']) ? $_ENV['DB_HOST'] : 'localhost';
        $port = isset($_ENV['DB_PORT']) ? $_ENV['DB_PORT'] : '3306';
        $dbname = isset($_ENV['DB_NAME']) ? $_ENV['DB_NAME'] : 'datafrete';
        $user = isset($_ENV['DB_USER']) ? $_ENV['DB_USER'] : 'root';
        $pass = isset($_ENV['DB_PASS']) ? $_ENV['DB_PASS'] : '';

        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
            $this->connection = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            throw new \Exception("Erro ao conectar ao banco de dados: " . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }
}

