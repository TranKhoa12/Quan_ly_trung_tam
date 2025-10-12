<?php

class CertificateController extends BaseController
{
    private $certificateModel;
    private $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->certificateModel = new Certificate();
        $this->userModel = new User();
        $this->requireAuth(); // Yêu cầu đăng nhập
    }

    public function index()
    {
        try {
            $user = $this->getUser();
            
            // Staff chỉ thấy chứng nhận của mình trong ngày, Admin thấy tất cả
            if ($user['role'] === 'staff') {
                $certificates = $this->certificateModel->getTodayCertificatesByStaff($user['id']);
            } else {
                $certificates = $this->certificateModel->getCertificatesWithDetails();
            }
            
            $this->view('certificates/index', [
                'certificates' => $certificates,
                'userRole' => $user['role']
            ]);
        } catch (Exception $e) {
            $this->view('certificates/index', [
                'certificates' => [], 
                'error' => $e->getMessage(),
                'userRole' => $this->getUser()['role'] ?? 'staff'
            ]);
        }
    }

    public function create()
    {
        try {
            $this->view('certificates/create');
        } catch (Exception $e) {
            $this->view('certificates/create', ['error' => $e->getMessage()]);
        }
    }

    public function store()
    {
        try {
            $data = [
                'student_name' => $_POST['student_name'] ?? '',
                'username' => $_POST['username'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'subject' => $_POST['subject'] ?? '',
                'receive_status' => $_POST['receive_status'] ?? 'not_received',
                'approval_status' => 'pending',
                'notes' => $_POST['notes'] ?? '',
                'requested_by' => 1 // Should be current user ID
            ];

            $certificateId = $this->certificateModel->create($data);

            header('Location: /Quan_ly_trung_tam/public/certificates');
            exit;
        } catch (Exception $e) {
            $this->view('certificates/create', [
                'error' => $e->getMessage(),
                'old_data' => $_POST
            ]);
        }
    }

    public function approve($id)
    {
        // Chỉ admin mới được duyệt/từ chối chứng nhận
        $this->requireAdmin();
        
        try {
            $user = $this->getUser();
            $action = $_POST['action'] ?? '';
            $notes = $_POST['notes'] ?? '';

            if ($action === 'approve') {
                $this->certificateModel->approveCertificate($id, $user['id'], $notes);
                $message = 'Đã duyệt chứng nhận thành công';
            } elseif ($action === 'cancel') {
                $this->certificateModel->cancelCertificate($id, $user['id'], $notes);
                $message = 'Đã từ chối chứng nhận';
            } else {
                throw new Exception('Hành động không hợp lệ');
            }

            $this->json(['success' => true, 'message' => $message]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // API Methods
    public function apiIndex()
    {
        try {
            $certificates = $this->certificateModel->getCertificatesWithDetails();
            $this->json(['success' => true, 'data' => $certificates]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function apiStore()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            $data = [
                'student_name' => $input['student_name'] ?? '',
                'username' => $input['username'] ?? '',
                'phone' => $input['phone'] ?? '',
                'subject' => $input['subject'] ?? '',
                'receive_status' => $input['receive_status'] ?? 'not_received',
                'approval_status' => 'pending',
                'notes' => $input['notes'] ?? '',
                'requested_by' => 1 // Should be current user ID
            ];

            $certificateId = $this->certificateModel->create($data);

            $this->json(['success' => true, 'data' => ['id' => $certificateId]]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function apiUpdate($id)
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            $action = $input['action'] ?? '';
            $notes = $input['notes'] ?? '';
            $approvedBy = 1; // Should be current user ID

            if ($action === 'approve') {
                $this->certificateModel->approveCertificate($id, $approvedBy, $notes);
            } elseif ($action === 'cancel') {
                $this->certificateModel->cancelCertificate($id, $approvedBy, $notes);
            } elseif ($action === 'mark_received') {
                $this->certificateModel->markReceived($id);
            }

            $this->json(['success' => true, 'message' => 'Cập nhật thành công']);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}