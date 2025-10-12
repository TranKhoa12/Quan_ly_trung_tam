<?php

class ReportController extends BaseController
{
    private $reportModel;
    private $reportCustomerModel;
    private $courseModel;
    private $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->reportModel = new Report();
        $this->reportCustomerModel = new ReportCustomer();
        $this->courseModel = new Course();
        $this->userModel = new User();
        $this->requireAuth(); // Yêu cầu đăng nhập
    }

    public function index()
    {
        try {
            $user = $this->getUser();
            
            // Staff chỉ thấy báo cáo của mình trong ngày, Admin thấy tất cả
            if ($user['role'] === 'staff') {
                // Nhân viên chỉ thấy báo cáo của mình trong ngày hiện tại
                $reports = $this->reportModel->getTodayReportsByStaff($user['id']);
                $staff = []; // Không cần danh sách staff cho nhân viên
            } else {
                // Admin có thể filter theo ngày và nhân viên
                $fromDate = $_GET['from_date'] ?? null;
                $toDate = $_GET['to_date'] ?? null;
                $staffId = $_GET['staff_id'] ?? null;
                
                if ($fromDate && $toDate) {
                    $reports = $this->reportModel->getReportsByDateRange($fromDate, $toDate);
                    if ($staffId) {
                        $reports = array_filter($reports, function($report) use ($staffId) {
                            return $report['staff_id'] == $staffId;
                        });
                    }
                } else {
                    $reports = $this->reportModel->getReportsWithStaff();
                    if ($staffId) {
                        $reports = array_filter($reports, function($report) use ($staffId) {
                            return $report['staff_id'] == $staffId;
                        });
                    }
                }
                
                $staff = $this->userModel->getStaffList();
            }
            
            $this->view('reports/index', [
                'reports' => $reports,
                'userRole' => $user['role'],
                'staff' => $staff ?? []
            ]);
        } catch (Exception $e) {
            $this->view('reports/index', [
                'reports' => [], 
                'error' => $e->getMessage(),
                'userRole' => $this->getUser()['role'] ?? 'staff',
                'staff' => []
            ]);
        }
    }

    public function create()
    {
        try {
            $user = $this->getUser();
            $courses = $this->courseModel->getActiveCourses();
            
            // Admin có thể chọn nhân viên, Staff chỉ tạo cho mình
            if ($user['role'] === 'admin') {
                $staff = $this->userModel->getStaffList();
            } else {
                $staff = [$user]; // Chỉ hiển thị chính mình
            }
            
            $this->view('reports/create', [
                'courses' => $courses,
                'staff' => $staff,
                'userRole' => $user['role']
            ]);
        } catch (Exception $e) {
            $this->view('reports/create', [
                'courses' => [],
                'staff' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    public function store()
    {
        try {
            $user = $this->getUser();
            
            // Debug: Log POST data
            error_log('POST data: ' . print_r($_POST, true));
            
            // Xác định staff_id dựa trên role
            if ($user['role'] === 'admin') {
                $staffId = $_POST['staff_id'] ?? $user['id'];
            } else {
                // Nhân viên chỉ có thể tạo báo cáo cho chính mình
                $staffId = $user['id'];
            }
            
            // Kiểm tra nếu là báo cáo rỗng
            $isEmptyReport = isset($_POST['empty_report']) && $_POST['empty_report'] == '1';
            
            if ($isEmptyReport) {
                // Báo cáo rỗng: số lượng đến = 0, số lượng chốt = 0
                $data = [
                    'report_date' => $_POST['report_date'] ?? date('Y-m-d'),
                    'report_time' => $_POST['report_time'] ?? date('H:i:s'),
                    'staff_id' => $staffId,
                    'total_visitors' => 0,
                    'total_registered' => 0,
                    'notes' => $_POST['notes'] ?? 'Báo cáo rỗng - Không có khách hàng đến'
                ];
            } else {
                // Báo cáo thông thường
                $data = [
                    'report_date' => $_POST['report_date'] ?? date('Y-m-d'),
                    'report_time' => $_POST['report_time'] ?? date('H:i:s'),
                    'staff_id' => $staffId,
                    'total_visitors' => $_POST['total_visitors'] ?? 0,
                    'total_registered' => $_POST['total_registered'] ?? 0,
                    'notes' => $_POST['notes'] ?? ''
                ];
            }

            $reportId = $this->reportModel->create($data);

            // Handle customers from JSON data if provided (chỉ cho báo cáo thông thường)
            if (!$isEmptyReport && isset($_POST['customers_data'])) {
                $customers = json_decode($_POST['customers_data'], true);
                if (is_array($customers)) {
                    foreach ($customers as $customer) {
                        if (!empty($customer['phone'])) {
                            $customerData = [
                                'report_id' => $reportId,
                                'phone' => $customer['phone'],
                                'full_name' => $customer['full_name'] ?? '',
                                'status' => $customer['status'] ?? 'new',
                                'course_id' => null, // Will be handled separately if needed
                                'registration_status' => $customer['registration_status'] ?? 'not_registered',
                                'payment_method' => $customer['payment_method'] ?? null,
                                'notes' => $customer['notes'] ?? ''
                            ];
                            $this->reportCustomerModel->create($customerData);
                        }
                    }
                }
            }
            // Handle customers from form array if provided (backward compatibility)
            elseif (isset($_POST['customers']) && is_array($_POST['customers'])) {
                foreach ($_POST['customers'] as $customer) {
                    if (!empty($customer['phone']) || !empty($customer['full_name'])) {
                        $customerData = [
                            'report_id' => $reportId,
                            'phone' => $customer['phone'] ?? '',
                            'full_name' => $customer['full_name'] ?? '',
                            'status' => $customer['status'] ?? 'new',
                            'course_id' => !empty($customer['course_id']) ? $customer['course_id'] : null,
                            'registration_status' => $customer['registration_status'] ?? 'not_registered',
                            'payment_method' => $customer['payment_method'] ?? null,
                            'notes' => $customer['notes'] ?? ''
                        ];
                        $this->reportCustomerModel->create($customerData);
                    }
                }
            }

            // Thiết lập thông báo thành công
            if ($isEmptyReport) {
                $_SESSION['success'] = 'Đã tạo báo cáo rỗng thành công cho ngày ' . $data['report_date'];
            } else {
                $_SESSION['success'] = 'Báo cáo đã được tạo thành công!';
            }

            header('Location: /Quan_ly_trung_tam/public/reports');
            exit;
        } catch (Exception $e) {
            $courses = $this->courseModel->getActiveCourses();
            $staff = $this->userModel->getStaffList();
            $this->view('reports/create', [
                'courses' => $courses,
                'staff' => $staff,
                'error' => $e->getMessage(),
                'old_data' => $_POST
            ]);
        }
    }

    public function show($id)
    {
        try {
            $user = $this->getUser();
            $report = $this->reportModel->getReportWithDetails($id);
            
            if (!$report) {
                throw new Exception('Báo cáo không tồn tại');
            }
            
            // Staff chỉ được xem báo cáo của mình
            if ($user['role'] === 'staff' && $report['staff_id'] != $user['id']) {
                throw new Exception('Bạn không có quyền xem báo cáo này');
            }
            
            $customers = $this->reportCustomerModel->getCustomersByReport($id);

            $this->view('reports/show', [
                'report' => $report,
                'customers' => $customers,
                'userRole' => $user['role']
            ]);
        } catch (Exception $e) {
            $this->view('reports/show', [
                'report' => null,
                'customers' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    // API Methods
    public function apiIndex()
    {
        try {
            $reports = $this->reportModel->getReportsWithStaff();
            $this->json(['success' => true, 'data' => $reports]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function apiStore()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            $data = [
                'report_date' => $input['report_date'] ?? date('Y-m-d'),
                'report_time' => $input['report_time'] ?? date('H:i:s'),
                'staff_id' => $input['staff_id'] ?? 1,
                'total_visitors' => $input['total_visitors'] ?? 0,
                'total_registered' => $input['total_registered'] ?? 0,
                'notes' => $input['notes'] ?? ''
            ];

            $reportId = $this->reportModel->create($data);

            // Handle customers if provided
            if (isset($input['customers']) && is_array($input['customers'])) {
                foreach ($input['customers'] as $customer) {
                    if (!empty($customer['phone']) || !empty($customer['full_name'])) {
                        $customerData = [
                            'report_id' => $reportId,
                            'phone' => $customer['phone'] ?? '',
                            'full_name' => $customer['full_name'] ?? '',
                            'status' => $customer['status'] ?? 'new',
                            'course_id' => !empty($customer['course_id']) ? $customer['course_id'] : null,
                            'registration_status' => $customer['registration_status'] ?? 'not_registered',
                            'payment_method' => $customer['payment_method'] ?? null,
                            'notes' => $customer['notes'] ?? ''
                        ];
                        $this->reportCustomerModel->create($customerData);
                    }
                }
            }

            $this->json(['success' => true, 'data' => ['id' => $reportId]]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function apiShow($id)
    {
        try {
            $user = $this->getUser();
            $report = $this->reportModel->getReportWithDetails($id);
            
            if (!$report) {
                $this->json(['success' => false, 'message' => 'Báo cáo không tồn tại'], 404);
                return;
            }
            
            // Staff chỉ được xem báo cáo của mình
            if ($user['role'] === 'staff' && $report['staff_id'] != $user['id']) {
                $this->json(['success' => false, 'message' => 'Bạn không có quyền xem báo cáo này'], 403);
                return;
            }
            
            $customers = $this->reportCustomerModel->getCustomersByReport($id);

            $this->json([
                'success' => true, 
                'data' => [
                    'report' => $report,
                    'customers' => $customers
                ]
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function apiUpdate($id)
    {
        try {
            // Chỉ admin mới được sửa báo cáo
            $user = $this->getUser();
            if ($user['role'] !== 'admin') {
                $this->json(['success' => false, 'message' => 'Bạn không có quyền sửa báo cáo'], 403);
                return;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            $data = [
                'report_date' => $input['report_date'],
                'report_time' => $input['report_time'],
                'staff_id' => $input['staff_id'],
                'total_visitors' => $input['total_visitors'],
                'total_registered' => $input['total_registered'],
                'notes' => $input['notes'] ?? ''
            ];

            $updated = $this->reportModel->update($id, $data);
            
            if (!$updated) {
                $this->json(['success' => false, 'message' => 'Không thể cập nhật báo cáo'], 400);
                return;
            }

            $this->json(['success' => true, 'message' => 'Cập nhật thành công']);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function apiDelete($id)
    {
        try {
            // Chỉ admin mới được xóa báo cáo
            $user = $this->getUser();
            if ($user['role'] !== 'admin') {
                $this->json(['success' => false, 'message' => 'Bạn không có quyền xóa báo cáo'], 403);
                return;
            }
            
            // Delete customers first (foreign key constraint)
            $this->reportCustomerModel->deleteByReport($id);
            
            // Delete report
            $deleted = $this->reportModel->delete($id);
            
            if (!$deleted) {
                $this->json(['success' => false, 'message' => 'Không thể xóa báo cáo'], 400);
                return;
            }

            $this->json(['success' => true, 'message' => 'Xóa thành công']);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}