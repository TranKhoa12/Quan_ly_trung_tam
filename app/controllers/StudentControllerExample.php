<?php

/**
 * VÍ DỤ: Tích hợp AdminLogger vào StudentController
 * File này là ví dụ minh họa cách sử dụng AdminLogger
 * Bạn có thể áp dụng tương tự cho các controller khác
 */

require_once __DIR__ . '/../helpers/AdminLogger.php';
use App\Helpers\AdminLogger;

class StudentControllerExample extends BaseController
{
    private $studentModel;
    private $logger;

    public function __construct()
    {
        parent::__construct();
        $this->studentModel = new Student();
        $this->requireAuth();
        $this->requireAdmin();
        
        // Khởi tạo logger
        $this->logger = new AdminLogger(
            $this->db->getConnection(), 
            $_SESSION['user_id'], 
            $_SESSION['username']
        );
    }

    /**
     * Xem danh sách học viên
     */
    public function index()
    {
        try {
            // Ghi log xem danh sách
            $this->logger->logView('students', 'Xem danh sách học viên', [
                'page' => $_GET['page'] ?? 1,
                'filter' => $_GET['filter'] ?? 'all'
            ]);

            $students = $this->studentModel->getStudentsWithDetails();
            $this->view('students/index', ['students' => $students]);
        } catch (Exception $e) {
            $this->view('students/index', ['students' => [], 'error' => $e->getMessage()]);
        }
    }

    /**
     * Tạo học viên mới
     */
    public function store()
    {
        try {
            $data = [
                'full_name' => $_POST['full_name'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'email' => $_POST['email'] ?? '',
                'course_id' => $_POST['course_id'] ?? null,
                'instructor_id' => $_POST['instructor_id'] ?? null,
                'enrollment_date' => $_POST['enrollment_date'] ?? null,
                'status' => $_POST['status'] ?? 'studying'
            ];

            // Tạo học viên
            $studentId = $this->studentModel->create($data);

            // Ghi log tạo mới
            $this->logger->logCreate('students', "Tạo học viên mới: {$data['full_name']}", [
                'student_id' => $studentId,
                'full_name' => $data['full_name'],
                'phone' => $data['phone'],
                'email' => $data['email'],
                'course_id' => $data['course_id']
            ]);

            $_SESSION['success'] = 'Thêm học viên thành công';
            header('Location: /Quan_ly_trung_tam/public/students');
            exit;
        } catch (Exception $e) {
            $this->view('students/create', [
                'error' => $e->getMessage(),
                'old_data' => $_POST
            ]);
        }
    }

    /**
     * Cập nhật học viên
     */
    public function update($id)
    {
        try {
            // Lấy dữ liệu cũ trước khi update
            $oldData = $this->studentModel->findById($id);
            
            if (!$oldData) {
                throw new Exception('Không tìm thấy học viên');
            }

            // Dữ liệu mới
            $newData = [
                'full_name' => $_POST['full_name'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'email' => $_POST['email'] ?? '',
                'course_id' => $_POST['course_id'] ?? null,
                'instructor_id' => $_POST['instructor_id'] ?? null,
                'enrollment_date' => $_POST['enrollment_date'] ?? null,
                'completion_date' => $_POST['completion_date'] ?? null,
                'status' => $_POST['status'] ?? 'studying'
            ];

            // Update
            $this->studentModel->update($id, $newData);

            // Ghi log cập nhật với so sánh old vs new
            $this->logger->logUpdate(
                'students', 
                "Cập nhật học viên ID: {$id} - {$newData['full_name']}", 
                $oldData,  // Dữ liệu cũ
                $newData   // Dữ liệu mới
            );

            $_SESSION['success'] = 'Cập nhật học viên thành công';
            header('Location: /Quan_ly_trung_tam/public/students');
            exit;
        } catch (Exception $e) {
            $this->view('students/edit', [
                'error' => $e->getMessage(),
                'student' => $oldData ?? null
            ]);
        }
    }

    /**
     * Xóa học viên
     */
    public function delete($id)
    {
        try {
            // Lấy thông tin học viên trước khi xóa
            $student = $this->studentModel->findById($id);
            
            if (!$student) {
                throw new Exception('Không tìm thấy học viên');
            }

            // Xóa
            $this->studentModel->delete($id);

            // Ghi log xóa với thông tin học viên đã xóa
            $this->logger->logDelete(
                'students', 
                "Xóa học viên ID: {$id} - {$student['full_name']}", 
                $student  // Lưu lại toàn bộ thông tin học viên đã xóa
            );

            $_SESSION['success'] = 'Xóa học viên thành công';
            header('Location: /Quan_ly_trung_tam/public/students');
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /Quan_ly_trung_tam/public/students');
            exit;
        }
    }

    /**
     * Xóa nhiều học viên cùng lúc
     */
    public function deleteMultiple()
    {
        try {
            $ids = $_POST['student_ids'] ?? [];
            
            if (empty($ids)) {
                throw new Exception('Chưa chọn học viên để xóa');
            }

            // Lấy thông tin các học viên sẽ xóa
            $students = $this->studentModel->findByIds($ids);
            $studentNames = array_column($students, 'full_name');

            // Xóa
            $deletedCount = $this->studentModel->deleteMultiple($ids);

            // Ghi log xóa nhiều
            $this->logger->logDelete(
                'students', 
                "Xóa {$deletedCount} học viên: " . implode(', ', $studentNames), 
                [
                    'student_ids' => $ids,
                    'deleted_count' => $deletedCount,
                    'students' => $students
                ]
            );

            $_SESSION['success'] = "Đã xóa {$deletedCount} học viên";
            header('Location: /Quan_ly_trung_tam/public/students');
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /Quan_ly_trung_tam/public/students');
            exit;
        }
    }

    /**
     * Xuất danh sách học viên ra Excel
     */
    public function export()
    {
        try {
            $filters = [
                'status' => $_GET['status'] ?? null,
                'course_id' => $_GET['course_id'] ?? null,
                'from_date' => $_GET['from_date'] ?? null,
                'to_date' => $_GET['to_date'] ?? null
            ];

            $students = $this->studentModel->getStudentsForExport($filters);

            // Ghi log xuất file
            $this->logger->logExport('students', 'Xuất danh sách học viên ra Excel', [
                'format' => 'xlsx',
                'total_records' => count($students),
                'filters' => $filters
            ]);

            // Generate Excel file...
            // Code xuất Excel...
            
            // Output file
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="students-' . date('Y-m-d-His') . '.xlsx"');
            // ... output Excel content
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /Quan_ly_trung_tam/public/students');
            exit;
        }
    }

    /**
     * Import học viên từ Excel
     */
    public function import()
    {
        try {
            if (!isset($_FILES['file'])) {
                throw new Exception('Chưa chọn file');
            }

            // Process import...
            $importedData = $this->processImportFile($_FILES['file']);
            $successCount = 0;
            $errors = [];

            foreach ($importedData as $row) {
                try {
                    $studentId = $this->studentModel->create($row);
                    $successCount++;
                } catch (Exception $e) {
                    $errors[] = "Dòng {$row['row_number']}: {$e->getMessage()}";
                }
            }

            // Ghi log import
            $this->logger->logCreate('students', "Import {$successCount} học viên từ Excel", [
                'success_count' => $successCount,
                'error_count' => count($errors),
                'total_rows' => count($importedData),
                'errors' => $errors
            ]);

            $_SESSION['success'] = "Import thành công {$successCount} học viên";
            if (!empty($errors)) {
                $_SESSION['warning'] = implode('<br>', $errors);
            }
            
            header('Location: /Quan_ly_trung_tam/public/students');
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /Quan_ly_trung_tam/public/students');
            exit;
        }
    }

    /**
     * Thay đổi trạng thái học viên
     */
    public function changeStatus($id)
    {
        try {
            $oldData = $this->studentModel->findById($id);
            $newStatus = $_POST['status'] ?? '';

            $this->studentModel->updateStatus($id, $newStatus);

            // Ghi log thay đổi trạng thái
            $this->logger->logUpdate('students', "Thay đổi trạng thái học viên ID: {$id}", [
                'id' => $id,
                'full_name' => $oldData['full_name'],
                'old_status' => $oldData['status']
            ], [
                'id' => $id,
                'full_name' => $oldData['full_name'],
                'new_status' => $newStatus
            ]);

            $this->json(['success' => true, 'message' => 'Cập nhật trạng thái thành công']);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Gửi email cho học viên
     */
    public function sendEmail($id)
    {
        try {
            $student = $this->studentModel->findById($id);
            $subject = $_POST['subject'] ?? '';
            $message = $_POST['message'] ?? '';

            // Send email logic...
            // $this->sendEmailToStudent($student['email'], $subject, $message);

            // Ghi log gửi email
            $this->logger->log('other', 'students', "Gửi email cho học viên: {$student['full_name']}", [
                'student_id' => $id,
                'email' => $student['email'],
                'subject' => $subject
            ]);

            $this->json(['success' => true, 'message' => 'Gửi email thành công']);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
