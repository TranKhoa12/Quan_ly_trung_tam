<?php
/**
 * Application Configuration
 */

return [
    'app_name' => 'Quản Lý Trung Tâm',
    'app_url' => 'http://localhost/Quan_ly_trung_tam/public',
    'timezone' => 'Asia/Ho_Chi_Minh',
    
    'database' => [
        'host' => 'localhost',
        'database' => 'quan_ly_trung_tam',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    ],
    
    'upload' => [
        'max_size' => 10 * 1024 * 1024, // 10MB
        'allowed_types' => ['jpg', 'jpeg', 'png', 'pdf'],
        'upload_path' => BASE_PATH . '/storage/uploads/'
    ],
    
    'pagination' => [
        'per_page' => 20
    ]
];