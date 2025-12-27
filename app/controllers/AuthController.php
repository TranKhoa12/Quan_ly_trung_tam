<?php

require_once __DIR__ . '/../helpers/AdminLogger.php';

use App\Helpers\AdminLogger;

class AuthController extends BaseController
{
    private $passwordResetModel;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->passwordResetModel = new PasswordResetToken();
        // Don't check auth for login/logout actions
    }

    public function showLogin()
    {
        if ($this->isLoggedIn()) {
            $this->redirect('/Quan_ly_trung_tam/public/dashboard');
        }
        
        $this->view('auth/login');
    }

    public function login()
    {
        if ($this->isLoggedIn()) {
            $this->redirect('/Quan_ly_trung_tam/public/dashboard');
        }

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $errors = $this->validateInput($_POST, [
            'username' => ['required' => true, 'message' => 'Tên đăng nhập là bắt buộc'],
            'password' => ['required' => true, 'message' => 'Mật khẩu là bắt buộc']
        ]);

        if (!empty($errors)) {
            $this->view('auth/login', ['errors' => $errors]);
            return;
        }

        $user = $this->db->fetch(
            "SELECT * FROM users WHERE username = ? AND status = 'active'",
            [$username]
        );

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            
            // Ghi log đăng nhập thành công
            $logger = new AdminLogger($this->db->getConnection(), $user['id'], $user['username']);
            $logger->logLogin(true);
            
            $this->redirect('/Quan_ly_trung_tam/public/dashboard');
        } else {
            // Ghi log đăng nhập thất bại
            $logger = new AdminLogger($this->db->getConnection(), null, $username);
            $logger->logLogin(false);
            
            $error = 'Tên đăng nhập hoặc mật khẩu không đúng';
            $this->view('auth/login', ['error' => $error]);
        }
    }

    public function logout()
    {
        // Ghi log đăng xuất trước khi destroy session
        if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
            $logger = new AdminLogger($this->db->getConnection(), $_SESSION['user_id'], $_SESSION['username']);
            $logger->logLogout();
        }
        
        session_destroy();
        $this->redirect('/Quan_ly_trung_tam/public/login');
    }

    public function showForgotPassword()
    {
        if ($this->isLoggedIn()) {
            $this->redirect('/Quan_ly_trung_tam/public/dashboard');
        }
        
        $this->view('auth/forgot-password');
    }

    public function forgotPassword()
    {
        if ($this->isLoggedIn()) {
            $this->redirect('/Quan_ly_trung_tam/public/dashboard');
        }

        $identifier = $_POST['identifier'] ?? ''; // username or email

        $errors = $this->validateInput($_POST, [
            'identifier' => ['required' => true, 'message' => 'Tên đăng nhập hoặc email là bắt buộc']
        ]);

        if (!empty($errors)) {
            $this->view('auth/forgot-password', ['errors' => $errors]);
            return;
        }

        // Find user by username or email
        $user = $this->db->fetch(
            "SELECT * FROM users WHERE (username = ? OR email = ?) AND status = 'active'",
            [$identifier, $identifier]
        );

        if ($user) {
            // Generate reset token using model
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $this->passwordResetModel->createToken($user['id'], $token, $expiresAt);

            // In real application, send email here
            // For demo, we'll show the reset link
            $resetLink = "/Quan_ly_trung_tam/public/reset-password/$token";
            
            $this->view('auth/forgot-password', [
                'success' => true,
                'reset_link' => $resetLink,
                'message' => 'Đã tạo link đặt lại mật khẩu. Trong môi trường thực tế, link này sẽ được gửi qua email.'
            ]);
        } else {
            $this->view('auth/forgot-password', [
                'error' => 'Không tìm thấy tài khoản với thông tin này'
            ]);
        }
    }

    public function showResetPassword($token)
    {
        if ($this->isLoggedIn()) {
            $this->redirect('/Quan_ly_trung_tam/public/dashboard');
        }

        // Verify token using model
        $resetToken = $this->passwordResetModel->validateToken($token);

        if (!$resetToken) {
            $this->view('auth/reset-password', [
                'error' => 'Token không hợp lệ hoặc đã hết hạn',
                'invalid_token' => true
            ]);
            return;
        }

        $this->view('auth/reset-password', [
            'token' => $token,
            'user' => $resetToken
        ]);
    }

    public function resetPassword()
    {
        if ($this->isLoggedIn()) {
            $this->redirect('/Quan_ly_trung_tam/public/dashboard');
        }

        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $errors = $this->validateInput($_POST, [
            'token' => ['required' => true, 'message' => 'Token là bắt buộc'],
            'password' => ['required' => true, 'min' => 6, 'message' => 'Mật khẩu phải có ít nhất 6 ký tự'],
            'confirm_password' => ['required' => true, 'message' => 'Xác nhận mật khẩu là bắt buộc']
        ]);

        if ($password !== $confirmPassword) {
            $errors['confirm_password'] = 'Mật khẩu xác nhận không khớp';
        }

        if (!empty($errors)) {
            $this->view('auth/reset-password', [
                'errors' => $errors,
                'token' => $token
            ]);
            return;
        }

        // Verify token
        $resetToken = $this->passwordResetModel->validateToken($token);

        if (!$resetToken) {
            $this->view('auth/reset-password', [
                'error' => 'Token không hợp lệ hoặc đã hết hạn',
                'invalid_token' => true
            ]);
            return;
        }

        // Update password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $this->db->query(
            "UPDATE users SET password = ? WHERE id = ?",
            [$hashedPassword, $resetToken['user_id']]
        );

        // Mark token as used using model
        $this->passwordResetModel->markAsUsed($resetToken['id']);

        $this->view('auth/reset-password', [
            'success' => true,
            'message' => 'Mật khẩu đã được đặt lại thành công. Bạn có thể đăng nhập với mật khẩu mới.'
        ]);
    }
}