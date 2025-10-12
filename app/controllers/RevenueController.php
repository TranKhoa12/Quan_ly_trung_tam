<?php

class RevenueController extends BaseController
{
    private $revenueModel;
    private $courseModel;
    private $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->revenueModel = new RevenueReport();
        $this->courseModel = new Course();
        $this->userModel = new User();
        $this->requireAuth(); // Yêu cầu đăng nhập
    }

    public function index()
    {
        try {
            $user = $this->getUser();
            
            // Staff chỉ thấy doanh thu của mình trong ngày, Admin thấy tất cả
            if ($user['role'] === 'staff') {
                $revenue_reports = $this->revenueModel->getTodayRevenueByStaff($user['id']);
            } else {
                $revenue_reports = $this->revenueModel->getRevenueWithDetails();
            }
            
            $this->view('revenue/index', [
                'revenue_reports' => $revenue_reports,
                'userRole' => $user['role']
            ]);
        } catch (Exception $e) {
            $this->view('revenue/index', [
                'revenue_reports' => [], 
                'error' => $e->getMessage(),
                'userRole' => $this->getUser()['role'] ?? 'staff'
            ]);
        }
    }

    public function create()
    {
        try {
            $courses = $this->courseModel->getActiveCourses();
            $staff = $this->userModel->getStaffList();
            $this->view('revenue/create', [
                'courses' => $courses,
                'staff' => $staff
            ]);
        } catch (Exception $e) {
            $this->view('revenue/create', [
                'courses' => [],
                'staff' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    public function store()
    {
        try {
            $data = [
                'payment_date' => $_POST['payment_date'] ?? date('Y-m-d'),
                'transfer_type' => $_POST['transfer_type'] ?? 'cash',
                'receipt_code' => $_POST['receipt_code'] ?? '',
                'amount' => $_POST['amount'] ?? 0,
                'student_name' => $_POST['student_name'] ?? '',
                'course_id' => !empty($_POST['course_id']) ? $_POST['course_id'] : null,
                'payment_content' => $_POST['payment_content'] ?? 'full_payment',
                'staff_id' => $_POST['staff_id'] ?? 1,
                'notes' => $_POST['notes'] ?? ''
            ];

            // Handle file upload
            if (isset($_FILES['confirmation_image']) && $_FILES['confirmation_image']['error'] === UPLOAD_ERR_OK) {
                try {
                    $fileName = $this->uploadFile($_FILES['confirmation_image'], ['jpg', 'jpeg', 'png', 'pdf']);
                    $data['confirmation_image'] = $fileName;
                } catch (Exception $e) {
                    throw new Exception('Lỗi upload file: ' . $e->getMessage());
                }
            }

            $revenueId = $this->revenueModel->create($data);

            header('Location: /Quan_ly_trung_tam/public/revenue');
            exit;
        } catch (Exception $e) {
            $courses = $this->courseModel->getActiveCourses();
            $staff = $this->userModel->getStaffList();
            $this->view('revenue/create', [
                'courses' => $courses,
                'staff' => $staff,
                'error' => $e->getMessage(),
                'old_data' => $_POST
            ]);
        }
    }

    // API Methods
    public function apiIndex()
    {
        try {
            $revenues = $this->revenueModel->getRevenueWithDetails();
            $this->json(['success' => true, 'data' => $revenues]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function apiStore()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            $data = [
                'payment_date' => $input['payment_date'] ?? date('Y-m-d'),
                'transfer_type' => $input['transfer_type'] ?? 'cash',
                'receipt_code' => $input['receipt_code'] ?? '',
                'amount' => $input['amount'] ?? 0,
                'student_name' => $input['student_name'] ?? '',
                'course_id' => !empty($input['course_id']) ? $input['course_id'] : null,
                'payment_content' => $input['payment_content'] ?? 'full_payment',
                'staff_id' => $input['staff_id'] ?? 1,
                'notes' => $input['notes'] ?? ''
            ];

            $revenueId = $this->revenueModel->create($data);

            $this->json(['success' => true, 'data' => ['id' => $revenueId]]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function apiShow($id)
    {
        try {
            $revenue = $this->revenueModel->find($id);
            
            if (!$revenue) {
                $this->json(['success' => false, 'message' => 'Báo cáo doanh thu không tồn tại'], 404);
                return;
            }

            $this->json(['success' => true, 'data' => $revenue]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}