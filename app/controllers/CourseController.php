<?php

class CourseController extends BaseController
{
    private $courseModel;

    public function __construct()
    {
        parent::__construct();
        require_once __DIR__ . '/../models/Course.php';
        $this->courseModel = new CourseModel();
        $this->requireAuth(); // Bật authentication
        $this->requireAdmin(); // Chỉ admin mới được truy cập
    }

    // Hiển thị danh sách khóa học
    public function index()
    {
        // Lấy tham số filter và phân trang
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 10;
        
        // Lấy danh sách khóa học với phân trang
        $courses = $this->courseModel->getAllCourses($search, $status, $page, $perPage);
        
        // Lấy tổng số khóa học để tính phân trang
        $totalCourses = $this->courseModel->getTotalCourses($search, $status);
        $totalPages = ceil($totalCourses / $perPage);
        
        // Lấy thống kê
        $stats = $this->courseModel->getCourseStats();
        
        $data = [
            'title' => 'Quản lý khóa học',
            'courses' => $courses,
            'stats' => $stats,
            'currentSearch' => $search,
            'currentStatus' => $status,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalCourses' => $totalCourses,
            'perPage' => $perPage
        ];
        
        $this->view('courses/index', $data);
    }

    // Hiển thị form tạo khóa học mới
    public function create()
    {
        $data = [
            'title' => 'Thêm khóa học mới'
        ];
        
        $this->view('courses/create', $data);
    }

    // Lưu khóa học mới
    public function store()
    {
        // Debug log
        error_log("CourseController::store() called");
        error_log("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
        error_log("POST data: " . print_r($_POST, true));
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log("Not POST method, redirecting to courses list");
            $this->redirect('/Quan_ly_trung_tam/public/courses');
        }

        $data = [
            'course_code' => $_POST['course_code'] ?? '',
            'course_name' => $_POST['course_name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'duration' => $_POST['duration'] ?? '',
            'fee' => $_POST['fee'] ?? '',
            'start_date' => $_POST['start_date'] ?? '',
            'end_date' => $_POST['end_date'] ?? '',
            'max_students' => $_POST['max_students'] ?? '',
            'status' => $_POST['status'] ?? 'active'
        ];

        error_log("Data to insert: " . print_r($data, true));

        // Validate required fields
        if (empty($data['course_code']) || empty($data['course_name'])) {
            error_log("Validation failed: Missing required fields");
            $_SESSION['error_message'] = 'Mã khóa học và tên khóa học là bắt buộc!';
            $this->redirect('/Quan_ly_trung_tam/public/courses/create');
        }

        try {
            error_log("Calling courseModel->createCourse()");
            $result = $this->courseModel->createCourse($data);
            error_log("Result: " . ($result ? 'true' : 'false'));
            
            if ($result) {
                error_log("Setting success message and redirecting to courses list");
                $_SESSION['success_message'] = 'Thêm khóa học thành công!';
                $this->redirect('/Quan_ly_trung_tam/public/courses');
            } else {
                error_log("Setting error message and redirecting to create form");
                $_SESSION['error_message'] = 'Có lỗi xảy ra khi thêm khóa học!';
                $this->redirect('/Quan_ly_trung_tam/public/courses/create');
            }
        } catch (Exception $e) {
            error_log("Exception: " . $e->getMessage());
            $_SESSION['error_message'] = 'Lỗi: ' . $e->getMessage();
            $this->redirect('/Quan_ly_trung_tam/public/courses/create');
        }
    }

    // Hiển thị chi tiết khóa học
    public function show($id)
    {
        $course = $this->courseModel->getCourseById($id);
        
        if (!$course) {
            $_SESSION['error_message'] = 'Không tìm thấy khóa học!';
            $this->redirect('/courses');
        }

        $data = [
            'title' => 'Chi tiết khóa học',
            'course' => $course
        ];
        
        $this->view('courses/show', $data);
    }

    // Hiển thị form chỉnh sửa
    public function edit($id)
    {
        $course = $this->courseModel->getCourseById($id);
        
        if (!$course) {
            $_SESSION['error_message'] = 'Không tìm thấy khóa học!';
            $this->redirect('/courses');
        }

        $data = [
            'title' => 'Chỉnh sửa khóa học',
            'course' => $course
        ];
        
        $this->view('courses/edit', $data);
    }

    // Cập nhật khóa học
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/courses');
        }

        $data = [
            'course_code' => $_POST['course_code'] ?? '',
            'course_name' => $_POST['course_name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'duration' => $_POST['duration'] ?? '',
            'fee' => $_POST['fee'] ?? '',
            'start_date' => $_POST['start_date'] ?? '',
            'end_date' => $_POST['end_date'] ?? '',
            'max_students' => $_POST['max_students'] ?? '',
            'status' => $_POST['status'] ?? 'active'
        ];

        // Validate required fields
        if (empty($data['course_code']) || empty($data['course_name'])) {
            $_SESSION['error_message'] = 'Mã khóa học và tên khóa học là bắt buộc!';
            $this->redirect('/courses/' . $id . '/edit');
        }

        try {
            $result = $this->courseModel->updateCourse($id, $data);
            if ($result) {
                $_SESSION['success_message'] = 'Cập nhật khóa học thành công!';
                $this->redirect('/courses');
            } else {
                $_SESSION['error_message'] = 'Có lỗi xảy ra khi cập nhật khóa học!';
                $this->redirect('/courses/' . $id . '/edit');
            }
        } catch (Exception $e) {
            $_SESSION['error_message'] = 'Lỗi: ' . $e->getMessage();
            $this->redirect('/courses/' . $id . '/edit');
        }
    }

    // Xóa khóa học
    public function delete($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/courses');
        }

        try {
            $result = $this->courseModel->deleteCourse($id);
            if ($result) {
                $_SESSION['success_message'] = 'Xóa khóa học thành công!';
            } else {
                $_SESSION['error_message'] = 'Có lỗi xảy ra khi xóa khóa học!';
            }
        } catch (Exception $e) {
            $_SESSION['error_message'] = 'Lỗi: ' . $e->getMessage();
        }
        
        $this->redirect('/courses');
    }

    // Hiển thị form import Excel
    public function import()
    {
        $data = [
            'title' => 'Import khóa học từ Excel'
        ];
        
        $this->view('courses/import', $data);
    }

    // Xử lý import Excel
    public function processImport()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/Quan_ly_trung_tam/public/courses/import');
        }

        // Kiểm tra file upload
        if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error_message'] = 'Vui lòng chọn file Excel để upload!';
            $this->redirect('/Quan_ly_trung_tam/public/courses/import');
        }

        $file = $_FILES['excel_file'];
        $skipDuplicates = isset($_POST['skip_duplicates']);

        // Kiểm tra loại file
        $allowedTypes = ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        
        if (!in_array(strtolower($fileExtension), ['xlsx', 'xls'])) {
            $_SESSION['error_message'] = 'File phải có định dạng .xlsx hoặc .xls!';
            $this->redirect('/Quan_ly_trung_tam/public/courses/import');
        }

        // Kiểm tra kích thước file (10MB)
        if ($file['size'] > 10 * 1024 * 1024) {
            $_SESSION['error_message'] = 'File quá lớn! Kích thước tối đa là 10MB.';
            $this->redirect('/Quan_ly_trung_tam/public/courses/import');
        }

        try {
            // Load PhpSpreadsheet
            require_once __DIR__ . '/../../vendor/autoload.php';
            
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file['tmp_name']);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            if (empty($rows)) {
                $_SESSION['error_message'] = 'File Excel trống hoặc không có dữ liệu!';
                $this->redirect('/Quan_ly_trung_tam/public/courses/import');
            }

            // Lấy header (dòng đầu tiên)
            $headers = array_map('trim', $rows[0]);
            
            // Kiểm tra các cột bắt buộc
            $requiredColumns = ['course_code', 'course_name'];
            $missingColumns = [];
            
            foreach ($requiredColumns as $column) {
                if (!in_array($column, $headers)) {
                    $missingColumns[] = $column;
                }
            }
            
            if (!empty($missingColumns)) {
                $_SESSION['error_message'] = 'Thiếu các cột bắt buộc: ' . implode(', ', $missingColumns);
                $this->redirect('/Quan_ly_trung_tam/public/courses/import');
            }

            // Xử lý dữ liệu
            $successCount = 0;
            $errorCount = 0;
            $duplicateCount = 0;
            $errors = [];

            for ($i = 1; $i < count($rows); $i++) {
                if ($i > 1000) { // Giới hạn 1000 dòng
                    break;
                }
                
                $row = $rows[$i];
                
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }
                
                // Map data theo header
                $courseData = [];
                foreach ($headers as $index => $header) {
                    $courseData[$header] = isset($row[$index]) ? trim($row[$index]) : '';
                }
                
                // Validate required fields
                if (empty($courseData['course_code']) || empty($courseData['course_name'])) {
                    $errorCount++;
                    $errors[] = "Dòng " . ($i + 1) . ": Thiếu mã khóa học hoặc tên khóa học";
                    continue;
                }
                
                // Kiểm tra trùng lặp nếu cần
                if ($skipDuplicates) {
                    $existing = $this->courseModel->getCourseByCode($courseData['course_code']);
                    if ($existing) {
                        $duplicateCount++;
                        continue;
                    }
                }
                
                // Prepare data for insert
                $insertData = [
                    'course_code' => $courseData['course_code'],
                    'course_name' => $courseData['course_name'],
                    'description' => $courseData['description'] ?? '',
                    'duration' => $courseData['duration_hours'] ?? '',
                    'fee' => $courseData['price'] ?? '',
                    'start_date' => $courseData['start_date'] ?? '',
                    'end_date' => $courseData['end_date'] ?? '',
                    'max_students' => $courseData['max_students'] ?? '',
                    'status' => 'active'
                ];
                
                try {
                    $result = $this->courseModel->createCourse($insertData);
                    if ($result) {
                        $successCount++;
                    } else {
                        $errorCount++;
                        $errors[] = "Dòng " . ($i + 1) . ": Không thể lưu khóa học";
                    }
                } catch (Exception $e) {
                    $errorCount++;
                    $errors[] = "Dòng " . ($i + 1) . ": " . $e->getMessage();
                }
            }

            // Tạo thông báo kết quả
            $message = "Import hoàn thành! ";
            $message .= "Thành công: {$successCount}, ";
            $message .= "Lỗi: {$errorCount}";
            
            if ($duplicateCount > 0) {
                $message .= ", Bỏ qua trùng lặp: {$duplicateCount}";
            }
            
            if (!empty($errors)) {
                $message .= "<br><br>Chi tiết lỗi:<br>" . implode("<br>", array_slice($errors, 0, 10));
                if (count($errors) > 10) {
                    $message .= "<br>... và " . (count($errors) - 10) . " lỗi khác";
                }
            }
            
            if ($successCount > 0) {
                $_SESSION['success_message'] = $message;
            } else {
                $_SESSION['error_message'] = $message;
            }

        } catch (Exception $e) {
            $_SESSION['error_message'] = 'Lỗi đọc file Excel: ' . $e->getMessage();
        }

        $this->redirect('/Quan_ly_trung_tam/public/courses');
    }

    // Download template Excel
    public function downloadTemplate()
    {
        try {
            require_once __DIR__ . '/../../vendor/autoload.php';
            
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set headers
            $headers = [
                'A1' => 'course_code',
                'B1' => 'course_name', 
                'C1' => 'description',
                'D1' => 'duration_hours',
                'E1' => 'price',
                'F1' => 'start_date',
                'G1' => 'end_date',
                'H1' => 'max_students'
            ];
            
            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }
            
            // Sample data
            $sheet->setCellValue('A2', 'ENG101');
            $sheet->setCellValue('B2', 'Tiếng Anh cơ bản');
            $sheet->setCellValue('C2', 'Khóa học tiếng Anh cho người mới bắt đầu');
            $sheet->setCellValue('D2', '40');
            $sheet->setCellValue('E2', '2000000');
            $sheet->setCellValue('F2', '2025-01-15');
            $sheet->setCellValue('G2', '2025-03-15');
            $sheet->setCellValue('H2', '25');
            
            // Style header
            $headerRange = 'A1:H1';
            $sheet->getStyle($headerRange)->getFont()->setBold(true);
            $sheet->getStyle($headerRange)->getFill()
                  ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                  ->getStartColor()->setRGB('E3F2FD');
            
            // Auto size columns
            foreach (range('A', 'H') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            // Output file
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="template_import_courses.xlsx"');
            header('Cache-Control: max-age=0');
            
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
            
        } catch (Exception $e) {
            $_SESSION['error_message'] = 'Lỗi tạo file template: ' . $e->getMessage();
            $this->redirect('/Quan_ly_trung_tam/public/courses/import');
        }
    }

    // Export Excel
    public function export()
    {
        try {
            require_once __DIR__ . '/../../vendor/autoload.php';
            
            // Lấy tất cả khóa học
            $courses = $this->courseModel->getAllCourses('', '');
            
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set headers
            $headers = [
                'A1' => 'Mã khóa học',
                'B1' => 'Tên khóa học',
                'C1' => 'Mô tả', 
                'D1' => 'Thời lượng (giờ)',
                'E1' => 'Học phí (VNĐ)',
                'F1' => 'Ngày bắt đầu',
                'G1' => 'Ngày kết thúc',
                'H1' => 'Số học viên tối đa',
                'I1' => 'Trạng thái',
                'J1' => 'Ngày tạo'
            ];
            
            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }
            
            // Add data
            $row = 2;
            foreach ($courses as $course) {
                $sheet->setCellValue('A' . $row, $course['course_code']);
                $sheet->setCellValue('B' . $row, $course['course_name']);
                $sheet->setCellValue('C' . $row, $course['description']);
                $sheet->setCellValue('D' . $row, $course['duration_hours']);
                $sheet->setCellValue('E' . $row, $course['price']);
                $sheet->setCellValue('F' . $row, $course['start_date']);
                $sheet->setCellValue('G' . $row, $course['end_date']);
                $sheet->setCellValue('H' . $row, $course['max_students']);
                
                $statusText = '';
                switch($course['status']) {
                    case 'active': $statusText = 'Đang hoạt động'; break;
                    case 'inactive': $statusText = 'Ngừng hoạt động'; break;
                    case 'completed': $statusText = 'Đã hoàn thành'; break;
                }
                $sheet->setCellValue('I' . $row, $statusText);
                $sheet->setCellValue('J' . $row, $course['created_at']);
                
                $row++;
            }
            
            // Style header
            $headerRange = 'A1:J1';
            $sheet->getStyle($headerRange)->getFont()->setBold(true);
            $sheet->getStyle($headerRange)->getFill()
                  ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                  ->getStartColor()->setRGB('E3F2FD');
            
            // Auto size columns
            foreach (range('A', 'J') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            // Output file
            $filename = 'danh_sach_khoa_hoc_' . date('Y-m-d_H-i-s') . '.xlsx';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
            
        } catch (Exception $e) {
            $_SESSION['error_message'] = 'Lỗi export file Excel: ' . $e->getMessage();
            $this->redirect('/Quan_ly_trung_tam/public/courses');
        }
    }
}
