<?php

class ProfileController extends BaseController
{
    private $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->userModel = new User();
    }

    public function index()
    {
        $userId = $_SESSION['user_id'];
        $user = $this->userModel->find($userId);

        if (!$user) {
            // Should not happen if logged in, but just in case
            $this->logout();
        }

        $data = ['user' => $user];
        
        // Check for flash messages
        if (isset($_SESSION['success'])) {
            $data['success'] = $_SESSION['success'];
            unset($_SESSION['success']);
        }
        
        if (isset($_SESSION['error'])) {
            $data['error'] = $_SESSION['error'];
            unset($_SESSION['error']);
        }

        $this->view('profile/index', $data);
    }

    public function update()
    {
        $userId = $_SESSION['user_id'];
        $user = $this->userModel->find($userId);

        if (!$user) {
            $this->redirect('/Quan_ly_trung_tam/public/login');
        }

        $data = [
            'full_name' => $_POST['full_name'] ?? $user['full_name'],
            'email' => $_POST['email'] ?? $user['email'],
            'phone' => $_POST['phone'] ?? $user['phone'],
            'address' => $_POST['address'] ?? $user['address'] ?? ''
        ];

        // Basic validation
        if (empty($data['full_name'])) {
            $_SESSION['error'] = 'Họ tên không được để trống';
            $this->redirect('/Quan_ly_trung_tam/public/profile');
            return;
        }

        // Handle password change if provided
        if (!empty($_POST['password'])) {
            if ($_POST['password'] !== $_POST['password_confirm']) {
                $_SESSION['error'] = 'Mật khẩu xác nhận không khớp';
                $this->redirect('/Quan_ly_trung_tam/public/profile');
                return;
            }
            $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }

        // Update user
        if ($this->userModel->update($userId, $data)) {
            // Update session info if name changed
            $_SESSION['full_name'] = $data['full_name'];
            $_SESSION['success'] = 'Cập nhật thông tin thành công';
        } else {
            $_SESSION['error'] = 'Có lỗi xảy ra khi cập nhật thông tin';
        }
        
        $this->redirect('/Quan_ly_trung_tam/public/profile');
    }
}
