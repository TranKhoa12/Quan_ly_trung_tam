<?php

class StaffController extends BaseController {
    private $staffModel;
    
    public function __construct() {
        parent::__construct();
        $this->staffModel = new Staff();
        $this->requireAuth(); // Bật authentication
        $this->requireAdmin(); // Chỉ admin mới được truy cập
    }
    
    public function index() {
        $search = $_GET['search'] ?? '';
        $department = $_GET['department'] ?? '';
        $status = $_GET['status'] ?? '';
        
        $staffList = $this->staffModel->getAllStaff($search, $department, $status);
        $departments = $this->staffModel->getDepartments();
        $stats = $this->staffModel->getStaffStats();
        
        $this->view('staff/index', [
            'staffList' => $staffList,
            'departments' => $departments,
            'stats' => $stats,
            'search' => $search,
            'department' => $department,
            'status' => $status
        ]);
    }
    
    public function create() {
        $departments = $this->staffModel->getDepartments();
        
        $this->view('staff/create', [
            'departments' => $departments
        ]);
    }
    
    public function store() {
        try {
            $data = [
                'full_name' => $_POST['full_name'] ?? '',
                'username' => $_POST['username'] ?? '',
                'email' => $_POST['email'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'password' => $_POST['password'] ?? '',
                'address' => $_POST['address'] ?? '',
                'department' => $_POST['department'] ?? '',
                'hire_date' => $_POST['hire_date'] ?? '',
                'salary' => $_POST['salary'] ?? '',
                'status' => $_POST['status'] ?? 'active',
                'notes' => $_POST['notes'] ?? ''
            ];
            
            // Validation
            if (empty($data['full_name']) || empty($data['username']) || empty($data['email']) || empty($data['password'])) {
                throw new Exception('Vui lòng điền đầy đủ các trường bắt buộc');
            }
            
            if ($_POST['password'] !== $_POST['password_confirm']) {
                throw new Exception('Mật khẩu xác nhận không khớp');
            }
            
            $result = $this->staffModel->createStaff($data);
            
            if ($result) {
                header('Location: /Quan_ly_trung_tam/public/staff?success=created');
                exit;
            } else {
                throw new Exception('Không thể tạo nhân viên');
            }
        } catch (Exception $e) {
            header('Location: /Quan_ly_trung_tam/public/staff/create?error=' . urlencode($e->getMessage()));
            exit;
        }
    }
    
    public function show($id) {
        $staff = $this->staffModel->getStaffById($id);
        
        if (!$staff) {
            header('Location: /Quan_ly_trung_tam/public/staff?error=not_found');
            exit;
        }
        
        $this->view('staff/show', [
            'staff' => $staff
        ]);
    }
    
    public function edit($id) {
        $staff = $this->staffModel->getStaffById($id);
        $departments = $this->staffModel->getDepartments();
        
        if (!$staff) {
            header('Location: /Quan_ly_trung_tam/public/staff?error=not_found');
            exit;
        }
        
        $this->view('staff/edit', [
            'staff' => $staff,
            'departments' => $departments
        ]);
    }
    
    public function update($id) {
        try {
            $data = [
                'full_name' => $_POST['full_name'] ?? '',
                'username' => $_POST['username'] ?? '',
                'email' => $_POST['email'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'address' => $_POST['address'] ?? '',
                'department' => $_POST['department'] ?? '',
                'hire_date' => $_POST['hire_date'] ?? '',
                'salary' => $_POST['salary'] ?? '',
                'status' => $_POST['status'] ?? 'active',
                'notes' => $_POST['notes'] ?? ''
            ];
            
            // Handle password update
            if (!empty($_POST['password'])) {
                if ($_POST['password'] !== $_POST['password_confirm']) {
                    throw new Exception('Mật khẩu xác nhận không khớp');
                }
                $data['password'] = $_POST['password'];
            }
            
            $result = $this->staffModel->updateStaff($id, $data);
            
            if ($result) {
                header('Location: /Quan_ly_trung_tam/public/staff/' . $id . '?success=updated');
                exit;
            } else {
                throw new Exception('Không thể cập nhật nhân viên');
            }
        } catch (Exception $e) {
            header('Location: /Quan_ly_trung_tam/public/staff/' . $id . '/edit?error=' . urlencode($e->getMessage()));
            exit;
        }
    }
    
    public function delete($id) {
        try {
            $result = $this->staffModel->deleteStaff($id);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Đã xóa nhân viên thành công']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Không thể xóa nhân viên']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
?>