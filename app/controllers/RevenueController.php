<?php

// Ensure models are loaded properly
require_once __DIR__ . '/../models/RevenueReport.php';
require_once __DIR__ . '/../models/Course.php';
require_once __DIR__ . '/../models/User.php';

class RevenueController extends BaseController
{
    private $revenueModel;
    private $courseModel;
    private $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->revenueModel = new RevenueReport();
        $this->courseModel = new CourseModel();
        $this->userModel = new User();
        $this->requireAuth(); // Yêu cầu đăng nhập
    }

    public function index()
    {
        try {
            $user = $this->getUser();
            
            // Pagination settings
            $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $perPage = 20;
            $offset = ($page - 1) * $perPage;
            
            // Build filter conditions
            $conditions = [];
            
            // Date filters (chỉ Admin)
            if ($user['role'] === 'admin') {
                if (!empty($_GET['from_date'])) {
                    $conditions['payment_date_from'] = $_GET['from_date'];
                }
                if (!empty($_GET['to_date'])) {
                    $conditions['payment_date_to'] = $_GET['to_date'];
                }
            }
            
            // Transfer type filter
            if (!empty($_GET['transfer_type'])) {
                $conditions['transfer_type'] = $_GET['transfer_type'];
            }
            
            // Payment content filter
            if (!empty($_GET['payment_content'])) {
                $conditions['payment_content'] = $_GET['payment_content'];
            }
            
            // Search filter
            $search = !empty($_GET['search']) ? trim($_GET['search']) : null;
            
            // Staff chỉ thấy doanh thu của mình trong ngày, Admin thấy tất cả
            if ($user['role'] === 'staff') {
                $conditions['staff_id'] = $user['id'];
                // Nhân viên chỉ xem doanh thu trong ngày
                $conditions['DATE(payment_date)'] = date('Y-m-d');
            }
            
            // Get total count for pagination
            $totalRecords = $this->revenueModel->countRevenue($conditions, $search);
            $totalPages = ceil($totalRecords / $perPage);
            
            // Get revenue reports with filters and pagination
            $revenue_reports = $this->revenueModel->getRevenueWithFilters($conditions, $search, $offset, $perPage);
            
            $this->view('revenue/index', [
                'revenue_reports' => $revenue_reports,
                'userRole' => $user['role'],
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalRecords' => $totalRecords,
                'perPage' => $perPage
            ]);
        } catch (Exception $e) {
            $this->view('revenue/index', [
                'revenue_reports' => [], 
                'error' => $e->getMessage(),
                'userRole' => $this->getUser()['role'] ?? 'staff',
                'currentPage' => 1,
                'totalPages' => 0,
                'totalRecords' => 0,
                'perPage' => 20
            ]);
        }
    }

    public function show($id)
    {
        try {
            $user = $this->getUser();
            $revenue = $this->revenueModel->find($id);
            
            if (!$revenue) {
                $_SESSION['error'] = 'Không tìm thấy dữ liệu doanh thu';
                $this->redirect('/Quan_ly_trung_tam/public/revenue');
                return;
            }
            
            // Staff chỉ được xem doanh thu của mình
            if ($user['role'] === 'staff' && $revenue['staff_id'] != $user['id']) {
                $_SESSION['error'] = 'Bạn không có quyền xem dữ liệu này';
                $this->redirect('/Quan_ly_trung_tam/public/revenue');
                return;
            }
            
            // Get course name if course_id exists
            $courseName = null;
            if (!empty($revenue['course_id'])) {
                $course = $this->courseModel->find($revenue['course_id']);
                $courseName = $course['course_name'] ?? null;
            }
            
            // Get staff name
            $staffUser = $this->userModel->find($revenue['staff_id']);
            $staffName = $staffUser['full_name'] ?? 'Không xác định';
            
            $this->view('revenue/show', [
                'revenue' => $revenue,
                'courseName' => $courseName,
                'staffName' => $staffName,
                'userRole' => $user['role']
            ]);
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            $this->redirect('/Quan_ly_trung_tam/public/revenue');
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
        $uploadedFileName = null; // Track uploaded file for cleanup
        
        try {
            // Validate payment date <= today
            $paymentDate = $_POST['payment_date'] ?? date('Y-m-d');
            if (strtotime($paymentDate) > strtotime(date('Y-m-d'))) {
                throw new Exception('Ngày đóng học phí không được vượt quá ngày hôm nay!');
            }

            $data = [
                'payment_date' => $paymentDate,
                'transfer_type' => $_POST['transfer_type'] ?? 'cash',
                'receipt_code' => trim($_POST['receipt_code'] ?? ''),
                'amount' => str_replace(',', '', $_POST['amount'] ?? '0'), // Remove commas
                'student_name' => $_POST['student_name'] ?? '',
                'course_id' => !empty($_POST['course_id']) ? $_POST['course_id'] : null,
                'payment_content' => $_POST['payment_content'] ?? 'full_payment',
                'staff_id' => $_POST['staff_id'] ?? 1,
                'notes' => $_POST['notes'] ?? ''
            ];

            // Handle multiple file uploads BEFORE validation to preserve files
            $uploadedFileNames = [];
            if (isset($_FILES['confirmation_images']) && is_array($_FILES['confirmation_images']['name'])) {
                $fileCount = count($_FILES['confirmation_images']['name']);
                
                for ($i = 0; $i < $fileCount; $i++) {
                    // Check if file was uploaded successfully
                    if ($_FILES['confirmation_images']['error'][$i] === UPLOAD_ERR_OK) {
                        try {
                            // Create custom filename: ReceiptCode_StudentName_Date_Index
                            $studentName = $this->slugify($data['student_name']);
                            $dateStr = date('dmY', strtotime($data['payment_date']));
                            $receiptCodePart = !empty($data['receipt_code']) ? $data['receipt_code'] : 'NO_CODE';
                            
                            $ext = pathinfo($_FILES['confirmation_images']['name'][$i], PATHINFO_EXTENSION);
                            $customFileName = $receiptCodePart . '_' . $studentName . '_' . $dateStr . '_' . ($i + 1) . '.' . $ext;
                            
                            // Create temporary single file array for upload function
                            $singleFile = [
                                'name' => $_FILES['confirmation_images']['name'][$i],
                                'type' => $_FILES['confirmation_images']['type'][$i],
                                'tmp_name' => $_FILES['confirmation_images']['tmp_name'][$i],
                                'error' => $_FILES['confirmation_images']['error'][$i],
                                'size' => $_FILES['confirmation_images']['size'][$i]
                            ];
                            
                            $fileName = $this->uploadFileWithCustomName($singleFile, $customFileName, ['jpg', 'jpeg', 'png', 'pdf']);
                            $uploadedFileNames[] = $fileName;
                        } catch (Exception $e) {
                            // Clean up previously uploaded files on error
                            foreach ($uploadedFileNames as $uploaded) {
                                $this->deleteUploadedFile($uploaded);
                            }
                            throw new Exception('Lỗi upload file ' . ($i + 1) . ': ' . $e->getMessage());
                        }
                    }
                }
                
                // Store all images in both confirmation_image and confirmation_images
                if (!empty($uploadedFileNames)) {
                    $data['confirmation_image'] = $uploadedFileNames[0]; // First image for backward compatibility
                    $data['confirmation_images'] = json_encode($uploadedFileNames); // All images as JSON array
                }
            }

            // Check duplicate receipt code AFTER file upload
            if (!empty($data['receipt_code'])) {
                if ($this->revenueModel->checkReceiptCodeExists($data['receipt_code'])) {
                    // If duplicate found and files were uploaded, delete them
                    foreach ($uploadedFileNames as $uploaded) {
                        $this->deleteUploadedFile($uploaded);
                    }
                    throw new Exception('Mã phiếu thu "' . $data['receipt_code'] . '" đã tồn tại! Vui lòng sử dụng mã khác.');
                }
            }

            $this->revenueModel->create($data);

            $_SESSION['success'] = 'Tạo báo cáo doanh thu thành công!';
            $this->redirect('/Quan_ly_trung_tam/public/revenue');
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

    private function deleteUploadedFile($fileName)
    {
        if (defined('BASE_PATH')) {
            $filePath = BASE_PATH . '/public/uploads/' . $fileName;
        } else {
            $filePath = dirname(__DIR__, 2) . '/public/uploads/' . $fileName;
        }
        
        if (file_exists($filePath)) {
            @unlink($filePath);
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

    public function apiStoreFromReport()
    {
        try {
            // Log incoming request
            error_log("Revenue API called");
            error_log("POST data: " . print_r($_POST, true));
            error_log("FILES data: " . print_r($_FILES, true));
            error_log("Session user_id: " . ($_SESSION['user_id'] ?? 'not set'));
            
            // Validate required fields
            if (empty($_POST['amount']) || $_POST['amount'] <= 0) {
                $this->json(['success' => false, 'message' => 'Số tiền không hợp lệ'], 400);
                return;
            }
            
            if (empty($_POST['student_name'])) {
                $this->json(['success' => false, 'message' => 'Tên học viên không được để trống'], 400);
                return;
            }
            
            $data = [
                'payment_date' => $_POST['payment_date'] ?? date('Y-m-d'),
                'transfer_type' => $_POST['transfer_type'] ?? 'cash',
                'receipt_code' => $_POST['receipt_code'] ?? '',
                'amount' => floatval($_POST['amount']),
                'student_name' => trim($_POST['student_name']),
                'course_id' => !empty($_POST['course_id']) && is_numeric($_POST['course_id']) ? intval($_POST['course_id']) : null,
                'payment_content' => $_POST['payment_content'] ?? 'full_payment',
                'staff_id' => $_SESSION['user_id'] ?? 1,
                'notes' => $_POST['notes'] ?? ''
            ];

            error_log("Data to insert: " . print_r($data, true));

            // Handle file upload
            if (isset($_FILES['confirmation_image']) && $_FILES['confirmation_image']['error'] === UPLOAD_ERR_OK) {
                try {
                    $fileName = $this->uploadFile($_FILES['confirmation_image'], ['jpg', 'jpeg', 'png', 'pdf']);
                    $data['confirmation_image'] = $fileName;
                    error_log("File uploaded: " . $fileName);
                } catch (Exception $e) {
                    error_log("File upload error: " . $e->getMessage());
                    $this->json(['success' => false, 'message' => 'Lỗi upload file: ' . $e->getMessage()], 400);
                    return;
                }
            }

            $revenueId = $this->revenueModel->create($data);
            error_log("Revenue created with ID: " . $revenueId);

            $this->json([
                'success' => true, 
                'message' => 'Đã báo cáo doanh thu thành công!',
                'data' => ['id' => $revenueId]
            ]);
        } catch (Exception $e) {
            error_log("Revenue API error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    public function checkReceiptCode()
    {
        header('Content-Type: application/json');
        
        try {
            $receiptCode = $_GET['receipt_code'] ?? '';
            
            if (empty($receiptCode)) {
                echo json_encode(['exists' => false]);
                exit;
            }
            
            $exists = $this->revenueModel->checkReceiptCodeExists($receiptCode);
            
            echo json_encode(['exists' => $exists]);
        } catch (Exception $e) {
            echo json_encode(['exists' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
    
    public function processOCR()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            // Mock realistic banking data
            $bankingSamples = [
                [
                    'text' => "VIETCOMBANK\nChuyển tiền thành công\nSố tiền: 2,500,000 VNĐ\nĐến: NGUYEN VAN MINH\nNội dung: Học phí khóa học lập trình PHP\nThời gian: 21/10/2025 10:30",
                    'parsed' => [
                        'recipient_name' => 'NGUYEN VAN MINH',
                        'amount' => '2500000', 
                        'content' => 'Học phí khóa học lập trình PHP',
                        'bank' => 'VCB'
                    ]
                ],
                [
                    'text' => "TECHCOMBANK\nGiao dịch thành công\nSố tiền: 1,800,000 VND\nTên người nhận: TRAN THI HUONG\nNội dung CK: Thanh toán học phí JavaScript\nNgày GD: 21/10/2025",
                    'parsed' => [
                        'recipient_name' => 'TRAN THI HUONG',
                        'amount' => '1800000',
                        'content' => 'Thanh toán học phí JavaScript', 
                        'bank' => 'TCB'
                    ]
                ],
                [
                    'text' => "BIDV\nThông báo chuyển tiền\nSố tiền: 3,200,000đ\nTài khoản nhận: LE VAN HIEU\nLý do: Cọc học phí React Native\nTrạng thái: Thành công",
                    'parsed' => [
                        'recipient_name' => 'LE VAN HIEU', 
                        'amount' => '3200000',
                        'content' => 'Cọc học phí React Native',
                        'bank' => 'BIDV'
                    ]
                ]
            ];
            
            // Select random sample
            $sample = $bankingSamples[array_rand($bankingSamples)];
            
            echo json_encode([
                'success' => true,
                'raw_text' => $sample['text'],
                'bank_detected' => $sample['parsed']['bank'],
                'parsed_data' => $sample['parsed'],
                'confidence' => 'high',
                'processing_time' => '1.8s'
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }

    public function deleteMultiple()
    {
        try {
            $user = $this->getUser();
            
            // Only admin can delete revenue
            if ($user['role'] !== 'admin') {
                $this->json(['success' => false, 'message' => 'Bạn không có quyền xóa doanh thu']);
                return;
            }
            
            // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['ids']) || !is_array($input['ids']) || empty($input['ids'])) {
                $this->json(['success' => false, 'message' => 'Không có giao dịch nào được chọn']);
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
                    // Check if revenue exists
                    $revenue = $this->revenueModel->find($id);
                    if (!$revenue) {
                        $errors[] = "Giao dịch #$id không tồn tại";
                        continue;
                    }
                    
                    // Delete confirmation image if exists
                    if (!empty($revenue['confirmation_image'])) {
                        $this->deleteUploadedFile($revenue['confirmation_image']);
                    }
                    
                    // Delete revenue record
                    $deleted = $this->revenueModel->delete($id);
                    
                    if ($deleted) {
                        $deletedCount++;
                    } else {
                        $errors[] = "Không thể xóa giao dịch #$id";
                    }
                } catch (Exception $e) {
                    $errors[] = "Lỗi xóa giao dịch #$id: " . $e->getMessage();
                }
            }
            
            if ($deletedCount > 0) {
                $message = "Đã xóa thành công $deletedCount giao dịch";
                if (!empty($errors)) {
                    $message .= ". Một số giao dịch không thể xóa: " . implode(', ', $errors);
                }
                $this->json(['success' => true, 'message' => $message, 'deleted_count' => $deletedCount]);
            } else {
                $this->json(['success' => false, 'message' => 'Không thể xóa giao dịch. ' . implode(', ', $errors)]);
            }
            
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
    }
}