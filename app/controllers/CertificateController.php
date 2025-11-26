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

    public function show($id)
    {
        try {
            $certificate = $this->certificateModel->find($id);
            
            if (!$certificate) {
                throw new Exception('Không tìm thấy yêu cầu chứng nhận');
            }
            
            $this->view('certificates/show', ['certificate' => $certificate]);
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /Quan_ly_trung_tam/public/certificates');
            exit;
        }
    }

    public function edit($id)
    {
        try {
            $certificate = $this->certificateModel->find($id);
            
            if (!$certificate) {
                throw new Exception('Không tìm thấy yêu cầu chứng nhận');
            }
            
            $this->view('certificates/edit', ['certificate' => $certificate]);
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /Quan_ly_trung_tam/public/certificates');
            exit;
        }
    }

    public function update($id)
    {
        try {
            $certificate = $this->certificateModel->find($id);
            
            if (!$certificate) {
                throw new Exception('Không tìm thấy yêu cầu chứng nhận');
            }
            
            $data = [
                'student_name' => $_POST['student_name'] ?? '',
                'username' => $_POST['username'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'subject' => $_POST['subject'] ?? '',
                'notes' => $_POST['notes'] ?? ''
            ];
            
            // Validate required fields
            if (empty($data['student_name'])) {
                throw new Exception('Tên học viên không được để trống');
            }
            
            if (empty($data['subject'])) {
                throw new Exception('Môn học không được để trống');
            }
            
            $this->certificateModel->update($id, $data);
            
            $_SESSION['success'] = 'Cập nhật yêu cầu chứng nhận thành công';
            header('Location: /Quan_ly_trung_tam/public/certificates');
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /Quan_ly_trung_tam/public/certificates/' . $id . '/edit');
            exit;
        }
    }

    public function updateStatus($id)
    {
        try {
            $user = $this->getUser();
            $approval_status = $_POST['approval_status'] ?? '';
            
            if (empty($approval_status) || !in_array($approval_status, ['pending', 'approved', 'cancelled'])) {
                throw new Exception('Trạng thái không hợp lệ');
            }
            
            $this->certificateModel->updateApprovalStatus($id, $approval_status, $user['id']);
            
            $messages = [
                'approved' => 'Đã phê duyệt yêu cầu chứng nhận',
                'cancelled' => 'Đã hủy yêu cầu chứng nhận',
                'pending' => 'Đã chuyển về trạng thái Chờ duyệt'
            ];
            
            $_SESSION['success'] = $messages[$approval_status];
            
            header('Location: /Quan_ly_trung_tam/public/certificates');
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /Quan_ly_trung_tam/public/certificates');
            exit;
        }
    }

    public function updateReceiveStatus($id)
    {
        try {
            $receive_status = $_POST['receive_status'] ?? '';
            
            if (empty($receive_status) || !in_array($receive_status, ['received', 'not_received'])) {
                throw new Exception('Trạng thái không hợp lệ');
            }
            
            $this->certificateModel->updateReceiveStatus($id, $receive_status);
            
            $messages = [
                'received' => 'Đã đánh dấu chứng nhận đã được nhận',
                'not_received' => 'Đã chuyển về trạng thái Chưa nhận'
            ];
            
            $_SESSION['success'] = $messages[$receive_status];
            
            header('Location: /Quan_ly_trung_tam/public/certificates');
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /Quan_ly_trung_tam/public/certificates');
            exit;
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

    public function deleteMultiple()
    {
        try {
            $user = $this->getUser();
            
            // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['ids']) || !is_array($input['ids']) || empty($input['ids'])) {
                $this->json(['success' => false, 'message' => 'Không có yêu cầu nào được chọn']);
                return;
            }
            
            $ids = array_filter($input['ids'], function($id) {
                return is_numeric($id) && $id > 0;
            });
            
            if (empty($ids)) {
                $this->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
                return;
            }
            
            $deletedCount = 0;
            $errors = [];
            
            foreach ($ids as $id) {
                try {
                    // Check if certificate exists
                    $certificate = $this->certificateModel->find($id);
                    if (!$certificate) {
                        $errors[] = "Yêu cầu #$id không tồn tại";
                        continue;
                    }
                    
                    // Delete certificate record
                    $deleted = $this->certificateModel->delete($id);
                    
                    if ($deleted) {
                        $deletedCount++;
                    } else {
                        $errors[] = "Không thể xóa yêu cầu #$id";
                    }
                } catch (Exception $e) {
                    $errors[] = "Lỗi xóa yêu cầu #$id: " . $e->getMessage();
                }
            }
            
            if ($deletedCount > 0) {
                $message = "Đã xóa thành công $deletedCount yêu cầu chứng nhận";
                if (!empty($errors)) {
                    $message .= ". Một số yêu cầu không thể xóa: " . implode(', ', $errors);
                }
                $this->json(['success' => true, 'message' => $message, 'deleted_count' => $deletedCount]);
            } else {
                $this->json(['success' => false, 'message' => 'Không thể xóa yêu cầu. ' . implode(', ', $errors)]);
            }
            
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
    }
}

