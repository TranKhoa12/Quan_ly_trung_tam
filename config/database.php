<?php
/**
 * Database Configuration
 */

return [
    'host'     => $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost',
    'database' => $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'quan_ly_trung_tam',
    'username' => $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root',
    'password' => $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '',
    'charset'  => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];