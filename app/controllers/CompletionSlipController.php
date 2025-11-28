<?php

require_once __DIR__ . '/../models/CompletionSlip.php';
require_once __DIR__ . '/../models/Course.php';
require_once __DIR__ . '/../models/User.php';

class CompletionSlipController extends BaseController
{
    private $completionSlipModel;
    private $courseModel;

    public function __construct()
    {
        parent::__construct();
        $this->completionSlipModel = new CompletionSlip();
        $this->courseModel = new CourseModel();
        $this->requireAuth();
    }

    public function index()
    {
        $filters = [];

        try {
            $user = $this->getUser();
            if (!empty($_GET['course_id'])) {
                $filters['course_id'] = (int) $_GET['course_id'];
            }
            if (!empty($_GET['search'])) {
                $filters['search'] = trim($_GET['search']);
            }

            $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
            $perPage = 12;
            $offset = ($page - 1) * $perPage;

            $totalRecords = $this->completionSlipModel->countWithFilters($filters);
            $slips = $this->completionSlipModel->getAllWithRelations($filters, $perPage, $offset);
            $courses = $this->courseModel->getActiveCourses();

            $this->view('completion_slips/index', [
                'slips' => $slips,
                'courses' => $courses,
                'filters' => $filters,
                'userRole' => $user['role'] ?? 'staff',
                'userId' => $user['id'] ?? 0,
                'currentPage' => $page,
                'totalPages' => (int) ceil($totalRecords / $perPage),
                'totalRecords' => $totalRecords,
                'perPage' => $perPage
            ]);
        } catch (Exception $e) {
            error_log('CompletionSlip index error: ' . $e->getMessage());
            $courses = $this->courseModel->getActiveCourses();
            $user = $this->getUser();
            $this->view('completion_slips/index', [
                'slips' => [],
                'courses' => $courses,
                'filters' => $filters,
                'userRole' => $user['role'] ?? 'staff',
                'userId' => $user['id'] ?? 0,
                'currentPage' => 1,
                'totalPages' => 0,
                'totalRecords' => 0,
                'perPage' => 12,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function create()
    {
        $courses = $this->courseModel->getActiveCourses();
        $userModel = new User();
        $staffList = $userModel->getStaffList();
        
        $this->view('completion_slips/create', [
            'courses' => $courses,
            'staffList' => $staffList
        ]);
    }

    public function store()
    {
        $user = $this->getUser();
        $courses = $this->courseModel->getActiveCourses();
        $uploadedFiles = [];

        try {
            $studentName = trim($_POST['student_name'] ?? '');
            $courseId = !empty($_POST['course_id']) ? (int) $_POST['course_id'] : null;

            if ($studentName === '' || empty($courseId)) {
                throw new Exception('Vui lòng nhập đầy đủ tên học viên và chọn khóa học.');
            }

            $data = [
                'student_name' => $studentName,
                'phone' => null,
                'course_id' => $courseId,
                'teacher_name' => trim($_POST['teacher_name'] ?? ''),
                'notes' => trim($_POST['notes'] ?? ''),
                'created_by' => $user['id'] ?? null,
                'updated_by' => $user['id'] ?? null
            ];

            $uploadedFiles = $this->handleImageUploads($studentName);
            if (!empty($uploadedFiles)) {
                $data['image_files'] = json_encode($uploadedFiles);
            }

            $this->completionSlipModel->create($data);
            $_SESSION['success'] = 'Đã lưu phiếu hoàn thành học viên.';
            $this->redirect('/Quan_ly_trung_tam/public/completion-slips');
        } catch (Exception $e) {
            error_log('CompletionSlip store error: ' . $e->getMessage());
            if (!empty($uploadedFiles)) {
                $this->cleanupUploads($uploadedFiles);
            }
            $this->view('completion_slips/create', [
                'courses' => $courses,
                'error' => $e->getMessage(),
                'old_data' => $_POST
            ]);
        }
    }

    public function show($id)
    {
        try {
            $slip = $this->completionSlipModel->findWithRelations($id);
            if (!$slip) {
                throw new Exception('Phiếu không tồn tại.');
            }

            $images = !empty($slip['image_files'])
                ? (json_decode($slip['image_files'], true) ?: [])
                : [];

            $this->view('completion_slips/show', [
                'slip' => $slip,
                'images' => $images,
                'userRole' => $this->getUser()['role'] ?? 'staff'
            ]);
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            $this->redirect('/Quan_ly_trung_tam/public/completion-slips');
        }
    }

    public function edit($id)
    {
        $user = $this->getUser();
        
        try {
            $slip = $this->completionSlipModel->findWithRelations($id);
            if (!$slip) {
                throw new Exception('Phiếu không tồn tại.');
            }
            
            // Staff can only edit their own slips, admin can edit all
            if ($user['role'] !== 'admin' && (int)$slip['created_by'] !== (int)$user['id']) {
                throw new Exception('Bạn chỉ có thể chỉnh sửa phiếu do mình tạo.');
            }

            $courses = $this->courseModel->getActiveCourses();
            $images = !empty($slip['image_files']) ? (json_decode($slip['image_files'], true) ?: []) : [];
            $userModel = new User();
            $staffList = $userModel->getStaffList();

            $this->view('completion_slips/edit', [
                'slip' => $slip,
                'courses' => $courses,
                'images' => $images,
                'staffList' => $staffList
            ]);
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            $this->redirect('/Quan_ly_trung_tam/public/completion-slips');
        }
    }

    public function update($id)
    {
        $user = $this->getUser();
        $uploadedFiles = [];

        try {
            $slip = $this->completionSlipModel->find($id);
            if (!$slip) {
                throw new Exception('Phiếu không tồn tại.');
            }
            
            // Staff can only update their own slips, admin can update all
            if ($user['role'] !== 'admin' && (int)$slip['created_by'] !== (int)$user['id']) {
                throw new Exception('Bạn chỉ có thể cập nhật phiếu do mình tạo.');
            }

            $studentName = trim($_POST['student_name'] ?? '');
            $courseId = !empty($_POST['course_id']) ? (int) $_POST['course_id'] : null;

            if ($studentName === '' || empty($courseId)) {
                throw new Exception('Vui lòng nhập đầy đủ tên học viên và khóa học.');
            }

            $data = [
                'student_name' => $studentName,
                'course_id' => $courseId,
                'teacher_name' => trim($_POST['teacher_name'] ?? ''),
                'notes' => trim($_POST['notes'] ?? ''),
                'updated_by' => $this->getUser()['id'] ?? null
            ];

            $existingImages = !empty($slip['image_files']) ? (json_decode($slip['image_files'], true) ?: []) : [];
            $removeImages = !empty($_POST['remove_images']) ? (array) $_POST['remove_images'] : [];

            if (!empty($removeImages)) {
                foreach ($removeImages as $remove) {
                    $index = array_search($remove, $existingImages, true);
                    if ($index !== false) {
                        unset($existingImages[$index]);
                        $this->deleteUploadedFile($remove);
                    }
                }
                $existingImages = array_values($existingImages);
            }

            $uploadedFiles = $this->handleImageUploads($studentName);
            if (!empty($uploadedFiles)) {
                $existingImages = array_merge($existingImages, $uploadedFiles);
            }

            $data['image_files'] = !empty($existingImages) ? json_encode($existingImages) : null;

            $this->completionSlipModel->update($id, $data);
            $_SESSION['success'] = 'Đã cập nhật phiếu hoàn thành thành công.';
            $this->redirect('/Quan_ly_trung_tam/public/completion-slips');
        } catch (Exception $e) {
            error_log('CompletionSlip update error: ' . $e->getMessage());
            if (!empty($uploadedFiles)) {
                $this->cleanupUploads($uploadedFiles);
            }
            $_SESSION['error'] = $e->getMessage();
            $this->redirect('/Quan_ly_trung_tam/public/completion-slips/' . $id . '/edit');
        }
    }

    private function handleImageUploads($studentName)
    {
        if (!isset($_FILES['completion_images']) || !is_array($_FILES['completion_images']['name'])) {
            return [];
        }

        $uploadedFiles = [];
        $fileCount = count($_FILES['completion_images']['name']);
        $timestamp = date('Ymd_His');

        try {
            for ($i = 0; $i < $fileCount; $i++) {
                if ($_FILES['completion_images']['error'][$i] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($_FILES['completion_images']['name'][$i], PATHINFO_EXTENSION));
                    $customName = 'completion_' . $this->slugify($studentName) . '_' . $timestamp . '_' . ($i + 1) . '_' . uniqid() . '.' . $ext;

                    $singleFile = [
                        'name' => $_FILES['completion_images']['name'][$i],
                        'type' => $_FILES['completion_images']['type'][$i],
                        'tmp_name' => $_FILES['completion_images']['tmp_name'][$i],
                        'error' => $_FILES['completion_images']['error'][$i],
                        'size' => $_FILES['completion_images']['size'][$i]
                    ];

                    $uploadedFiles[] = $this->uploadFileWithCustomName($singleFile, $customName, ['jpg', 'jpeg', 'png', 'pdf']);
                }
            }
        } catch (Exception $e) {
            $this->cleanupUploads($uploadedFiles);
            throw $e;
        }

        return $uploadedFiles;
    }

    private function cleanupUploads(array $files)
    {
        foreach ($files as $file) {
            $this->deleteUploadedFile($file);
        }
    }

    private function deleteUploadedFile($fileName)
    {
        $filePath = defined('BASE_PATH')
            ? BASE_PATH . '/public/uploads/' . $fileName
            : dirname(__DIR__, 2) . '/public/uploads/' . $fileName;

        if (is_file($filePath)) {
            @unlink($filePath);
        }
    }

    private function slugify($text)
    {
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
        $text = preg_replace('/[^A-Za-z0-9]/', '', $text);

        return strtolower($text);
    }

    public function delete($id)
    {
        $user = $this->getUser();
        
        if (($user['role'] ?? 'staff') !== 'admin') {
            $_SESSION['error'] = 'Chỉ quản trị viên mới có thể xóa phiếu hoàn thành.';
            $this->redirect('/Quan_ly_trung_tam/public/completion-slips');
            return;
        }

        try {
            $slip = $this->completionSlipModel->find($id);
            if (!$slip) {
                throw new Exception('Không tìm thấy phiếu hoàn thành.');
            }

            // Delete associated images
            if (!empty($slip['image_files'])) {
                $images = json_decode($slip['image_files'], true);
                if (is_array($images)) {
                    foreach ($images as $image) {
                        $this->deleteUploadedFile($image);
                    }
                }
            }

            $this->completionSlipModel->delete($id);
            $_SESSION['success'] = 'Đã xóa phiếu hoàn thành thành công.';
        } catch (Exception $e) {
            error_log('CompletionSlip delete error: ' . $e->getMessage());
            $_SESSION['error'] = 'Không thể xóa phiếu: ' . $e->getMessage();
        }

        $this->redirect('/Quan_ly_trung_tam/public/completion-slips');
    }

    public function deleteMultiple()
    {
        try {
            $user = $this->getUser();
            
            if ($user['role'] !== 'admin') {
                $this->json(['success' => false, 'message' => 'Bạn không có quyền xóa phiếu hoàn thành']);
                return;
            }

            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['ids']) || !is_array($input['ids']) || empty($input['ids'])) {
                $this->json(['success' => false, 'message' => 'Không có phiếu nào được chọn']);
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
                    $slip = $this->completionSlipModel->find($id);
                    if (!$slip) {
                        $errors[] = "Phiếu #$id không tồn tại";
                        continue;
                    }

                    // Delete associated images
                    if (!empty($slip['image_files'])) {
                        $images = json_decode($slip['image_files'], true);
                        if (is_array($images)) {
                            foreach ($images as $image) {
                                $this->deleteUploadedFile($image);
                            }
                        }
                    }

                    $deleted = $this->completionSlipModel->delete($id);
                    if ($deleted) {
                        $deletedCount++;
                    } else {
                        $errors[] = "Không thể xóa phiếu #$id";
                    }
                } catch (Exception $inner) {
                    $errors[] = "Lỗi xóa phiếu #$id: " . $inner->getMessage();
                }
            }

            if ($deletedCount > 0) {
                $message = "Đã xóa thành công $deletedCount phiếu hoàn thành";
                if (!empty($errors)) {
                    $message .= ". Một số lỗi: " . implode(', ', array_slice($errors, 0, 3));
                }
                $this->json(['success' => true, 'message' => $message]);
            } else {
                $this->json(['success' => false, 'message' => 'Không thể xóa phiếu. Lỗi: ' . implode(', ', $errors)]);
            }
        } catch (Exception $e) {
            error_log('CompletionSlip deleteMultiple error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
        }
    }
}
