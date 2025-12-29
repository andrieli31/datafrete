<?php

$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $_ENV[trim($name)] = trim($value);
        }
    }
}

return
[
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/database/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/database/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'development',
        'development' => [
            'adapter' => 'mysql',
            'host' => isset($_ENV['DB_HOST']) ? $_ENV['DB_HOST'] : 'localhost',
            'name' => isset($_ENV['DB_NAME']) ? $_ENV['DB_NAME'] : 'datafrete',
            'user' => isset($_ENV['DB_USER']) ? $_ENV['DB_USER'] : 'root',
            'pass' => isset($_ENV['DB_PASS']) ? $_ENV['DB_PASS'] : '',
            'port' => isset($_ENV['DB_PORT']) ? $_ENV['DB_PORT'] : '3306',
            'charset' => 'utf8mb4',
        ]
    ],
    'version_order' => 'creation'
];

