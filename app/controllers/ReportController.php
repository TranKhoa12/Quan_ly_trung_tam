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
        
        // Load model files
        if (!class_exists('Report')) {
            require_once __DIR__ . '/../models/Report.php';
        }
        if (!class_exists('ReportCustomer')) {
            require_once __DIR__ . '/../models/ReportCustomer.php';
        }
        if (!class_exists('CourseModel')) {
            require_once __DIR__ . '/../models/Course.php';
        }
        if (!class_exists('User')) {
            require_once __DIR__ . '/../models/User.php';
        }
        
        $this->reportModel = new Report();
        $this->reportCustomerModel = new ReportCustomer();
        $this->courseModel = new CourseModel();
        $this->userModel = new User();
        $this->requireAuth(); // Yêu cầu đăng nhập
    }

    public function index()
    {
        try {
            $user = $this->getUser();
            
            // Phân quyền xem báo cáo
            if ($user['role'] === 'staff') {
                // Nhân viên CHỈ thấy báo cáo của mình hôm nay
                $reports = $this->reportModel->getTodayReportsByStaff($user['id']);
                $staff = []; // Không cần danh sách staff cho nhân viên
                $title = 'Báo cáo của tôi hôm nay';
            } else {
                // Admin có thể xem tất cả báo cáo và filter
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
                $title = 'Quản lý báo cáo học viên';
            }
            
            $this->view('reports/index', [
                'reports' => $reports,
                'userRole' => $user['role'],
                'staff' => $staff ?? [],
                'title' => $title,
                'user' => $user
            ]);
        } catch (Exception $e) {
            $this->view('reports/index', [
                'reports' => [], 
                'error' => $e->getMessage(),
                'userRole' => $this->getUser()['role'] ?? 'staff',
                'staff' => [],
                'title' => 'Báo cáo học viên'
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

            // Get revenue drafts first to map payment methods
            $revenueDraftsData = [];
            if (!$isEmptyReport && isset($_POST['revenue_drafts'])) {
                $revenueDrafts = json_decode($_POST['revenue_drafts'], true);
                if (is_array($revenueDrafts)) {
                    foreach ($revenueDrafts as $draft) {
                        $rowIndex = $draft['rowIndex'] ?? null;
                        if ($rowIndex !== null) {
                            $revenueDraftsData[$rowIndex] = $draft;
                        }
                    }
                }
            }
            
            // Handle customers from JSON data if provided (chỉ cho báo cáo thông thường)
            if (!$isEmptyReport && isset($_POST['customers_data'])) {
                $customers = json_decode($_POST['customers_data'], true);
                if (is_array($customers)) {
                    // Load RevenueReport model for creating revenue records
                    require_once __DIR__ . '/../models/RevenueReport.php';
                    $revenueModel = new RevenueReport();
                    $savedRevenueCount = 0;
                    
                    $customerIndex = 0;
                    foreach ($customers as $customer) {
                        if (!empty($customer['phone'])) {
                            // Get payment method from revenue data if exists
                            $paymentMethod = null;
                            $hasRevenue = isset($customer['has_revenue']) && $customer['has_revenue'] == '1';
                            $revenueData = null;
                            
                            if ($hasRevenue && !empty($customer['revenue_data'])) {
                                // Parse revenue_data JSON string
                                $revenueDataParsed = is_string($customer['revenue_data']) 
                                    ? json_decode($customer['revenue_data'], true) 
                                    : $customer['revenue_data'];
                                
                                if (is_array($revenueDataParsed)) {
                                    $revenueData = $revenueDataParsed;
                                    // Map transfer_type to payment_method label
                                    $transferTypeMap = [
                                        'cash' => 'Tiền mặt',
                                        'account_co_nhi' => 'TK Cô Nhi',
                                        'account_thay_hien' => 'TK Thầy Hiến',
                                        'account_company' => 'TK Công ty'
                                    ];
                                    $paymentMethod = $transferTypeMap[$revenueData['transfer_type'] ?? 'cash'] ?? null;
                                }
                            } elseif (isset($revenueDraftsData[$customerIndex])) {
                                // Fallback to old revenue_drafts method for backward compatibility
                                $transferType = $revenueDraftsData[$customerIndex]['transfer_type'] ?? null;
                                $transferTypeMap = [
                                    'cash' => 'Tiền mặt',
                                    'account_co_nhi' => 'TK Cô Nhi',
                                    'account_thay_hien' => 'TK Thầy Hiến',
                                    'account_company' => 'TK Công ty'
                                ];
                                $paymentMethod = $transferTypeMap[$transferType] ?? null;
                            }
                            
                            // Create customer record
                            $customerData = [
                                'report_id' => $reportId,
                                'phone' => $customer['phone'],
                                'full_name' => $customer['full_name'] ?? '',
                                'status' => $customer['status'] ?? 'new',
                                'course_id' => !empty($customer['course_id']) ? $customer['course_id'] : null,
                                'registration_status' => isset($customer['registered']) && $customer['registered'] == '1' ? 'registered' : 'not_registered',
                                'payment_method' => $paymentMethod,
                                'notes' => $customer['notes'] ?? ''
                            ];
                            $this->reportCustomerModel->create($customerData);
                            
                            // Create revenue record if revenue data exists
                            if ($hasRevenue && $revenueData) {
                                try {
                                    // Remove commas from amount
                                    $amount = str_replace(',', '', $revenueData['amount'] ?? '0');
                                    
                                    // Handle file upload for this revenue entry
                                    $confirmationImage = null;
                                    $fileFieldName = 'revenue_image_' . $customerIndex;
                                    if (isset($_FILES[$fileFieldName]) && $_FILES[$fileFieldName]['error'] === UPLOAD_ERR_OK) {
                                        try {
                                            $studentName = $this->slugify(trim($revenueData['student_name'] ?? $customer['full_name'] ?? ''));
                                            $dateStr = date('dmY', strtotime($revenueData['payment_date'] ?? date('Y-m-d')));
                                            $receiptCodePart = !empty($revenueData['receipt_code']) ? $revenueData['receipt_code'] : 'NO_CODE';
                                            
                                            $ext = pathinfo($_FILES[$fileFieldName]['name'], PATHINFO_EXTENSION);
                                            $customFileName = $receiptCodePart . '_' . $studentName . '_' . $dateStr . '.' . $ext;
                                            
                                            $confirmationImage = $this->uploadFileWithCustomName($_FILES[$fileFieldName], $customFileName, ['jpg', 'jpeg', 'png', 'pdf']);
                                        } catch (Exception $e) {
                                            error_log('Error uploading revenue image: ' . $e->getMessage());
                                        }
                                    }
                                    
                                    // Create revenue record
                                    $revenueRecord = [
                                        'payment_date' => $revenueData['payment_date'] ?? date('Y-m-d'),
                                        'transfer_type' => $revenueData['transfer_type'] ?? 'cash',
                                        'receipt_code' => $revenueData['receipt_code'] ?? '',
                                        'amount' => floatval($amount),
                                        'student_name' => trim($revenueData['student_name'] ?? $customer['full_name'] ?? ''),
                                        'course_id' => !empty($revenueData['course_id']) && is_numeric($revenueData['course_id']) 
                                            ? intval($revenueData['course_id']) 
                                            : (!empty($customer['course_id']) ? intval($customer['course_id']) : null),
                                        'payment_content' => $revenueData['payment_content'] ?? 'full_payment',
                                        'staff_id' => $staffId,
                                        'notes' => $revenueData['notes'] ?? '',
                                        'confirmation_image' => $confirmationImage
                                    ];
                                    
                                    if ($revenueRecord['amount'] > 0 && !empty($revenueRecord['student_name'])) {
                                        $revenueModel->create($revenueRecord);
                                        $savedRevenueCount++;
                                        error_log("Saved revenue from customer: " . print_r($revenueRecord, true));
                                    }
                                } catch (Exception $e) {
                                    error_log("Error saving revenue from customer: " . $e->getMessage());
                                }
                            }
                            
                            $customerIndex++;
                        }
                    }
                    
                    if ($savedRevenueCount > 0) {
                        error_log("Saved {$savedRevenueCount} revenue records from customers");
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

            // Handle revenue drafts if provided
            if (!$isEmptyReport && isset($_POST['revenue_drafts'])) {
                $revenueDrafts = json_decode($_POST['revenue_drafts'], true);
                if (is_array($revenueDrafts) && count($revenueDrafts) > 0) {
                    require_once __DIR__ . '/../models/RevenueReport.php';
                    $revenueModel = new RevenueReport();
                    
                    $savedCount = 0;
                    foreach ($revenueDrafts as $index => $draft) {
                        try {
                            // Remove commas from amount
                            $amount = str_replace(',', '', $draft['amount'] ?? '0');
                            
                            // Initialize confirmation_image as null
                            $confirmationImage = null;
                            
                            // Handle file upload for this revenue entry FIRST
                            $fileFieldName = 'revenue_image_' . $index;
                            if (isset($_FILES[$fileFieldName]) && $_FILES[$fileFieldName]['error'] === UPLOAD_ERR_OK) {
                                try {
                                    // Create custom filename using draft data
                                    $studentName = $this->slugify(trim($draft['student_name'] ?? ''));
                                    $dateStr = date('dmY', strtotime($draft['payment_date'] ?? date('Y-m-d')));
                                    $receiptCodePart = !empty($draft['receipt_code']) ? $draft['receipt_code'] : 'NO_CODE';
                                    
                                    $ext = pathinfo($_FILES[$fileFieldName]['name'], PATHINFO_EXTENSION);
                                    $customFileName = $receiptCodePart . '_' . $studentName . '_' . $dateStr . '.' . $ext;
                                    
                                    $confirmationImage = $this->uploadFileWithCustomName($_FILES[$fileFieldName], $customFileName, ['jpg', 'jpeg', 'png', 'pdf']);
                                } catch (Exception $e) {
                                    error_log('Error uploading revenue image: ' . $e->getMessage());
                                }
                            }
                            
                            // Build revenue data with uploaded image
                            $revenueData = [
                                'payment_date' => $draft['payment_date'] ?? date('Y-m-d'),
                                'transfer_type' => $draft['transfer_type'] ?? 'cash',
                                'receipt_code' => $draft['receipt_code'] ?? '',
                                'amount' => floatval($amount),
                                'student_name' => trim($draft['student_name'] ?? ''),
                                'course_id' => !empty($draft['course_id']) && is_numeric($draft['course_id']) ? intval($draft['course_id']) : null,
                                'payment_content' => $draft['payment_content'] ?? 'full_payment',
                                'staff_id' => $staffId,
                                'notes' => $draft['notes'] ?? '',
                                'confirmation_image' => $confirmationImage
                            ];
                            
                            if ($revenueData['amount'] > 0 && !empty($revenueData['student_name'])) {
                                $revenueModel->create($revenueData);
                                $savedCount++;
                                error_log("Saved revenue draft: " . print_r($revenueData, true));
                            }
                        } catch (Exception $e) {
                            error_log("Error saving revenue draft: " . $e->getMessage());
                        }
                    }
                    
                    error_log("Saved {$savedCount} revenue drafts out of " . count($revenueDrafts));
                }
            }
            
            // Thiết lập thông báo thành công
            if ($isEmptyReport) {
                $_SESSION['success'] = 'Đã tạo báo cáo rỗng thành công cho ngày ' . $data['report_date'];
            } else {
                // Count total revenue records saved (from both customers and old revenue_drafts)
                $totalRevenueCount = (isset($savedRevenueCount) ? $savedRevenueCount : 0) + (isset($savedCount) ? $savedCount : 0);
                $revenueCount = $totalRevenueCount > 0 ? " và {$totalRevenueCount} doanh thu" : '';
                $_SESSION['success'] = 'Báo cáo đã được tạo thành công' . $revenueCount . '!';
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

    public function edit($id)
    {
        try {
            $user = $this->getUser();
            
            // Only admin can edit reports
            if ($user['role'] !== 'admin') {
                throw new Exception('Bạn không có quyền chỉnh sửa báo cáo');
            }
            
            $report = $this->reportModel->getReportWithDetails($id);
            
            if (!$report) {
                throw new Exception('Báo cáo không tồn tại');
            }
            
            $customers = $this->reportCustomerModel->getCustomersByReport($id);
            $courses = $this->courseModel->getActiveCourses();

            $this->view('reports/edit', [
                'report' => $report,
                'customers' => $customers,
                'courses' => $courses,
                'userRole' => $user['role']
            ]);
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            $this->redirect('/reports');
        }
    }

    public function update($id)
    {
        try {
            $user = $this->getUser();
            
            // Only admin can update reports
            if ($user['role'] !== 'admin') {
                throw new Exception('Bạn không có quyền cập nhật báo cáo');
            }

            // Validate input
            if (empty($_POST['report_date'])) {
                throw new Exception('Ngày báo cáo là bắt buộc');
            }

            // Update report data
            $reportData = [
                'report_date' => $_POST['report_date'],
                'total_visitors' => $_POST['total_visitors'] ?? 0,
                'total_registered' => $_POST['total_registered'] ?? 0,
                'notes' => $_POST['notes'] ?? '',
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $updated = $this->reportModel->updateReport($id, $reportData);
            
            if (!$updated) {
                throw new Exception('Không thể cập nhật báo cáo');
            }

            // Update customers if provided
            if (isset($_POST['customers_data'])) {
                $customers = json_decode($_POST['customers_data'], true);
                if (is_array($customers)) {
                    // Delete existing customers
                    $this->reportCustomerModel->deleteByReport($id);
                    
                    // Add updated customers
                    foreach ($customers as $customer) {
                        if (!empty($customer['phone'])) {
                            $customerData = [
                                'report_id' => $id,
                                'phone' => $customer['phone'],
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
            }

            $_SESSION['success'] = 'Cập nhật báo cáo thành công!';
            $this->redirect('/reports/' . $id);
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            $this->redirect('/reports/' . $id . '/edit');
        }
    }

    public function delete($id)
    {
        try {
            $user = $this->getUser();
            
            // Only admin can delete reports
            if ($user['role'] !== 'admin') {
                throw new Exception('Bạn không có quyền xóa báo cáo');
            }
            
            // Check if report exists
            $report = $this->reportModel->find($id);
            if (!$report) {
                throw new Exception('Báo cáo không tồn tại');
            }
            
            // Delete customers first (foreign key constraint)
            $this->reportCustomerModel->deleteByReport($id);
            
            // Delete report
            $deleted = $this->reportModel->deleteReport($id);
            
            if (!$deleted) {
                throw new Exception('Không thể xóa báo cáo');
            }

            $_SESSION['success'] = 'Xóa báo cáo thành công!';
            $this->redirect('/reports');
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            $this->redirect('/reports');
        }
    }

    public function deleteMultiple()
    {
        try {
            $user = $this->getUser();
            
            // Only admin can delete reports
            if ($user['role'] !== 'admin') {
                $this->json(['success' => false, 'message' => 'Bạn không có quyền xóa báo cáo']);
                return;
            }
            
            // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['ids']) || !is_array($input['ids']) || empty($input['ids'])) {
                $this->json(['success' => false, 'message' => 'Không có báo cáo nào được chọn']);
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
                    // Check if report exists
                    $report = $this->reportModel->find($id);
                    if (!$report) {
                        $errors[] = "Báo cáo #$id không tồn tại";
                        continue;
                    }
                    
                    // Delete customers first (foreign key constraint)
                    $this->reportCustomerModel->deleteByReport($id);
                    
                    // Delete report
                    $deleted = $this->reportModel->deleteReport($id);
                    
                    if ($deleted) {
                        $deletedCount++;
                    } else {
                        $errors[] = "Không thể xóa báo cáo #$id";
                    }
                } catch (Exception $e) {
                    $errors[] = "Lỗi xóa báo cáo #$id: " . $e->getMessage();
                }
            }
            
            if ($deletedCount > 0) {
                $message = "Đã xóa thành công $deletedCount báo cáo";
                if (!empty($errors)) {
                    $message .= ". Một số báo cáo không thể xóa: " . implode(', ', $errors);
                }
                $this->json(['success' => true, 'message' => $message, 'deleted_count' => $deletedCount]);
            } else {
                $this->json(['success' => false, 'message' => 'Không thể xóa báo cáo. ' . implode(', ', $errors)]);
            }
            
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
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
            // Check authentication
            if (!$this->isLoggedIn()) {
                $this->json(['success' => false, 'message' => 'Vui lòng đăng nhập'], 401);
                return;
            }
            
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

            $updated = $this->reportModel->updateReport($id, $data);
            
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
            $deleted = $this->reportModel->deleteReport($id);
            
            if (!$deleted) {
                $this->json(['success' => false, 'message' => 'Không thể xóa báo cáo'], 400);
                return;
            }

            $this->json(['success' => true, 'message' => 'Xóa thành công']);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    private function slugify($text)
    {
        // Remove Vietnamese accents
        $text = preg_replace('/[àáạảãâầấậẩẫăằắặẳẵ]/u', 'a', $text);
        $text = preg_replace('/[èéẹẻẽêềếệểễ]/u', 'e', $text);
        $text = preg_replace('/[ìíịỉĩ]/u', 'i', $text);
        $text = preg_replace('/[òóọỏõôồốộổỗơờớợởỡ]/u', 'o', $text);
        $text = preg_replace('/[ùúụủũưừứựửữ]/u', 'u', $text);
        $text = preg_replace('/[ỳýỵỷỹ]/u', 'y', $text);
        $text = preg_replace('/đ/u', 'd', $text);
        $text = preg_replace('/[ÀÁẠẢÃÂẦẤẬẨẪĂẰẮẶẲẴ]/u', 'A', $text);
        $text = preg_replace('/[ÈÉẸẺẼÊỀẾỆỂỄ]/u', 'E', $text);
        $text = preg_replace('/[ÌÍỊỈĨ]/u', 'I', $text);
        $text = preg_replace('/[ÒÓỌỎÕÔỒỐỘỔỖƠỜỚỢỞỠ]/u', 'O', $text);
        $text = preg_replace('/[ÙÚỤỦŨƯỪỨỰỬỮ]/u', 'U', $text);
        $text = preg_replace('/[ỲÝỴỶỸ]/u', 'Y', $text);
        $text = preg_replace('/Đ/u', 'D', $text);
        
        // Remove special characters and spaces
        $text = preg_replace('/[^A-Za-z0-9]/', '', $text);
        
        return $text;
    }
}