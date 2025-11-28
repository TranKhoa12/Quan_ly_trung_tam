<?php
// Public page - Không cần đăng nhập
// Trang này cho phép học viên tự điền form yêu cầu chứng nhận

session_start();
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/BaseModel.php';
require_once __DIR__ . '/../app/models/Certificate.php';

$certificateModel = new Certificate();
$error = '';
$success = '';

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = [
            'student_name' => $_POST['student_name'] ?? '',
            'username' => $_POST['username'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'subject' => $_POST['subject'] ?? '',
            'email' => $_POST['email'] ?? '',
            'receive_status' => 'not_received',
            'approval_status' => 'pending',
            'notes' => $_POST['notes'] ?? '',
            'requested_by' => null // NULL = học viên tự điền
        ];

        // Validate required fields
        if (empty($data['student_name'])) {
            throw new Exception('Tên học viên không được để trống');
        }
        
        if (empty($data['username'])) {
            throw new Exception('Tên đăng nhập không được để trống');
        }
        
        if (empty($data['phone'])) {
            throw new Exception('Số điện thoại không được để trống');
        }
        
        if (empty($data['subject'])) {
            throw new Exception('Bộ môn không được để trống');
        }
        
        if (empty($data['email'])) {
            throw new Exception('Email không được để trống');
        }
        
        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email không hợp lệ');
        }

        $certificateId = $certificateModel->create($data);
        $success = 'Gửi yêu cầu thành công! Bạn sẽ nhận được email thông báo khi chứng nhận được phê duyệt.';
        
        // Clear POST data after successful submission
        $_POST = [];
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Load view
require_once __DIR__ . '/../app/views/public/certificate_request.php';
