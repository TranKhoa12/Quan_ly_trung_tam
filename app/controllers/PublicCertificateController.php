<?php

class PublicCertificateController extends BaseController
{
    private $certificateModel;

    public function __construct()
    {
        parent::__construct();
        if (!class_exists('Certificate')) {
            require_once __DIR__ . '/../models/Certificate.php';
        }
        $this->certificateModel = new Certificate();
    }

    public function showForm()
    {
        $data = [
            'success' => $_SESSION['public_cert_success'] ?? '',
            'error' => $_SESSION['public_cert_error'] ?? '',
            'old_data' => $_SESSION['public_cert_data'] ?? []
        ];

        unset($_SESSION['public_cert_success'], $_SESSION['public_cert_error'], $_SESSION['public_cert_data']);

        $this->view('public/certificate_request', $data);
    }

    public function submit()
    {
        try {
            $data = [
                'student_name' => trim($_POST['student_name'] ?? ''),
                'username' => trim($_POST['username'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'subject' => trim($_POST['subject'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'receive_status' => 'not_received',
                'approval_status' => 'pending',
                'notes' => trim($_POST['notes'] ?? ''),
                'requested_by' => null
            ];

            $this->validatePublicForm($data);

            $this->certificateModel->create($data);

            $_SESSION['public_cert_success'] = 'Yêu cầu của bạn đã được gửi thành công! Vui lòng chờ admin phê duyệt.';
            $_SESSION['public_cert_data'] = [];
        } catch (Exception $e) {
            $_SESSION['public_cert_error'] = $e->getMessage();
            $_SESSION['public_cert_data'] = $_POST;
        }

        header('Location: /Quan_ly_trung_tam/public/certificate-request');
        exit;
    }

    private function validatePublicForm($data)
    {
        $requiredFields = ['student_name', 'username', 'phone', 'subject', 'email'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new Exception('Vui lòng điền đầy đủ thông tin bắt buộc.');
            }
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email không hợp lệ.');
        }

        if (!preg_match('/^[0-9]{10,11}$/', $data['phone'])) {
            throw new Exception('Số điện thoại phải có 10-11 chữ số.');
        }
    }
}
