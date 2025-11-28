<?php

class CertificateController extends BaseController
{
    private $certificateModel;
    private $userModel;
    private $editLogModel;

    public function __construct()
    {
        parent::__construct();
        
        // Load required models
        if (!class_exists('Certificate')) {
            require_once __DIR__ . '/../models/Certificate.php';
        }
        if (!class_exists('User')) {
            require_once __DIR__ . '/../models/User.php';
        }
        if (!class_exists('CertificateEditLog')) {
            require_once __DIR__ . '/../models/CertificateEditLog.php';
        }
        
        $this->certificateModel = new Certificate();
        $this->userModel = new User();
        $this->editLogModel = new CertificateEditLog();
        $this->requireAuth(); // Yêu cầu đăng nhập
    }

    public function index()
    {
        try {
            $user = $this->getUser();
            
            // Lấy toàn bộ danh sách
            $certificates = $this->certificateModel->getCertificatesWithDetails();
            
            // Áp dụng các bộ lọc nếu có
            if (!empty($_GET['search'])) {
                $search = strtolower(trim($_GET['search']));
                $certificates = array_filter($certificates, function($cert) use ($search) {
                    return stripos($cert['student_name'], $search) !== false 
                        || stripos($cert['username'], $search) !== false
                        || stripos($cert['phone'], $search) !== false;
                });
            }
            
            if (!empty($_GET['approval_status'])) {
                $status = $_GET['approval_status'];
                $certificates = array_filter($certificates, function($cert) use ($status) {
                    return $cert['approval_status'] === $status;
                });
            }
            
            if (!empty($_GET['receive_status'])) {
                $status = $_GET['receive_status'];
                $certificates = array_filter($certificates, function($cert) use ($status) {
                    return $cert['receive_status'] === $status;
                });
            }
            
            // Reset array keys after filtering
            $certificates = array_values($certificates);
            
            // Phân trang
            $itemsPerPage = 10;
            $currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $totalItems = count($certificates);
            $totalPages = ceil($totalItems / $itemsPerPage);
            $offset = ($currentPage - 1) * $itemsPerPage;
            
            // Lấy dữ liệu cho trang hiện tại
            $paginatedCertificates = array_slice($certificates, $offset, $itemsPerPage);
            
            $this->view('certificates/index', [
                'certificates' => $paginatedCertificates,
                'userRole' => $user['role'],
                'currentPage' => $currentPage,
                'totalPages' => $totalPages,
                'totalItems' => $totalItems,
                'itemsPerPage' => $itemsPerPage
            ]);
        } catch (Exception $e) {
            error_log("Certificate index error: " . $e->getMessage());
            $this->view('certificates/index', [
                'certificates' => [], 
                'error' => $e->getMessage(),
                'userRole' => $this->getUser()['role'] ?? 'staff',
                'currentPage' => 1,
                'totalPages' => 0,
                'totalItems' => 0,
                'itemsPerPage' => 10
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
                'email' => $_POST['email'] ?? '',
                'receive_status' => $_POST['receive_status'] ?? 'not_received',
                'approval_status' => 'pending',
                'notes' => $_POST['notes'] ?? '',
                'requested_by' => $this->getUser()['id'] ?? null
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

            $certificateId = $this->certificateModel->create($data);

            $_SESSION['success'] = 'Tạo yêu cầu chứng nhận thành công';
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
            $user = $this->getUser();
            
            // Chỉ admin mới xem được chi tiết
            if ($user['role'] !== 'admin') {
                $_SESSION['error'] = 'Bạn không có quyền xem chi tiết yêu cầu chứng nhận';
                header('Location: /Quan_ly_trung_tam/public/certificates');
                exit;
            }
            
            $certificate = $this->certificateModel->findWithRelations($id);
            
            if (!$certificate) {
                throw new Exception('Không tìm thấy yêu cầu chứng nhận');
            }
            
            $logs = $this->editLogModel->getLogsByCertificate($id);
            
            $this->view('certificates/show', [
                'certificate' => $certificate,
                'userRole' => $user['role'] ?? 'staff',
                'userId' => $user['id'] ?? 0,
                'editLogs' => $logs
            ]);
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
            
            // Cho phép cả staff và admin sửa
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
            
            // Cho phép cả staff và admin sửa
            $user = $this->getUser();
            
            $data = [
                'student_name' => $_POST['student_name'] ?? '',
                'username' => $_POST['username'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'subject' => $_POST['subject'] ?? '',
                'email' => $_POST['email'] ?? '',
                'notes' => $_POST['notes'] ?? ''
            ];
            
            // Validate required fields
            if (empty($data['student_name'])) {
                throw new Exception('Tên học viên không được để trống');
            }
            
            if (empty($data['subject'])) {
                throw new Exception('Môn học không được để trống');
            }
            
            if (empty($data['email'])) {
                throw new Exception('Email không được để trống');
            }
            
            // Validate email format
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email không hợp lệ');
            }
            
            $changes = $this->buildChangeLog($certificate, $data);
            $this->certificateModel->update($id, $data);
            
            if (!empty($changes)) {
                $this->editLogModel->create([
                    'certificate_id' => $id,
                    'user_id' => $user['id'] ?? null,
                    'changes' => $changes
                ]);
            }
            
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
            
            // Chỉ admin mới được phê duyệt/hủy
            if ($user['role'] !== 'admin') {
                throw new Exception('Bạn không có quyền thực hiện thao tác này');
            }
            
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
            $user = $this->getUser();
            
            $receive_status = $_POST['receive_status'] ?? '';
            
            if (empty($receive_status) || !in_array($receive_status, ['received', 'not_received'])) {
                throw new Exception('Trạng thái không hợp lệ');
            }
            
            // Truyền userId để lưu người xác nhận
            $this->certificateModel->updateReceiveStatus($id, $receive_status, $user['id']);
            
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
            
            if ($user['role'] !== 'admin') {
                $this->json(['success' => false, 'message' => 'Bạn không có quyền xóa yêu cầu chứng nhận']);
                return;
            }

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
                    $certificate = $this->certificateModel->find($id);
                    if (!$certificate) {
                        $errors[] = "Yêu cầu #$id không tồn tại";
                        continue;
                    }

                    $deleted = $this->certificateModel->delete($id);
                    if ($deleted) {
                        $deletedCount++;
                    } else {
                        $errors[] = "Không thể xóa yêu cầu #$id";
                    }
                } catch (Exception $inner) {
                    $errors[] = "Lỗi xóa yêu cầu #$id: " . $inner->getMessage();
                }
            }

            if ($deletedCount > 0) {
                $message = "Đã xóa thành công $deletedCount yêu cầu chứng nhận";
                if (!empty($errors)) {
                    $message .= ". Một số yêu cầu không thể xóa: " . implode(', ', $errors);
                }
                $this->json(['success' => true, 'message' => $message, 'deleted_count' => $deletedCount]);
            } else {
                $errorMessage = !empty($errors) ? implode(', ', $errors) : 'Không thể xóa yêu cầu';
                $this->json(['success' => false, 'message' => 'Không thể xóa yêu cầu. ' . $errorMessage]);
            }
            
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
    }

    private function buildChangeLog($original, $updated)
    {
        $fields = [
            'student_name' => 'Tên học viên',
            'username' => 'Tên đăng nhập',
            'phone' => 'Số điện thoại',
            'subject' => 'Bộ môn',
            'email' => 'Email',
            'notes' => 'Ghi chú'
        ];
        
        $changes = [];
        foreach ($fields as $field => $label) {
            $old = trim((string)($original[$field] ?? ''));
            $new = trim((string)($updated[$field] ?? ''));
            if ($old !== $new) {
                $oldText = $old === '' ? '[Trống]' : $old;
                $newText = $new === '' ? '[Trống]' : $new;
                $changes[] = "$label: '$oldText' → '$newText'";
            }
        }
        
        return implode('; ', $changes);
    }
}

