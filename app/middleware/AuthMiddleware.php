<?php

class AuthMiddleware
{
    public static function check()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /Quan_ly_trung_tam/public/login');
            exit;
        }
    }

    public static function checkRole($requiredRole)
    {
        self::check();
        
        if ($_SESSION['role'] !== $requiredRole) {
            http_response_code(403);
            echo '<h1>403 - Forbidden</h1><p>Bạn không có quyền truy cập trang này.</p>';
            exit;
        }
    }

    public static function checkAdminOrStaff()
    {
        self::check();
        
        $allowedRoles = ['admin', 'staff'];
        if (!in_array($_SESSION['role'], $allowedRoles)) {
            http_response_code(403);
            echo '<h1>403 - Forbidden</h1><p>Bạn không có quyền truy cập trang này.</p>';
            exit;
        }
    }
}