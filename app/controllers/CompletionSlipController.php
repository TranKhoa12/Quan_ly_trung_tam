<?php

require_once __DIR__ . '/../models/CompletionSlip.php';
require_once __DIR__ . '/../models/Course.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Staff.php';

class CompletionSlipController extends BaseController
{
    private $completionSlipModel;
    private $courseModel;
    private $userModel;
    private $staffModel;
    private $staffList;

    public function __construct()
    {
        parent::__construct();
        $this->completionSlipModel = new CompletionSlip();
        $this->courseModel = new CourseModel();
        $this->userModel = new User();
        $this->staffModel = new Staff();
        $teachingStaff = $this->staffModel->getAllStaff('', '', 'active');
        $this->staffList = array_values(array_filter($teachingStaff, function ($staff) {
            if (empty($staff['department'])) {
                return false;
            }
            $dept = mb_strtolower(trim($staff['department']), 'UTF-8');
            return $dept === 'giảng dạy';
        }));
        $this->requireAuth();
    }

    public function index()
    {
        $filters = $this->buildFilters($_GET);

        try {
            $user = $this->getUser();

            $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
            $perPage = 12;
            $offset = ($page - 1) * $perPage;

            $totalRecords = $this->completionSlipModel->countWithFilters($filters);
            $slips = $this->completionSlipModel->getAllWithRelations($filters, $perPage, $offset);
            $courses = $this->courseModel->getActiveCourses();
            $creators = $this->completionSlipModel->getDistinctCreators();

            $this->view('completion_slips/index', [
                'slips' => $slips,
                'courses' => $courses,
                'creators' => $creators,
                'staffList' => $this->staffList,
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
            $creators = $this->completionSlipModel->getDistinctCreators();
            $user = $this->getUser();
            $this->view('completion_slips/index', [
                'slips' => [],
                'courses' => $courses,
                'creators' => $creators,
                'staffList' => $this->staffList,
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

        $this->view('completion_slips/create', [
            'courses' => $courses,
            'staffList' => $this->staffList
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
                'staffList' => $this->staffList,
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

            $this->view('completion_slips/edit', [
                'slip' => $slip,
                'courses' => $courses,
                'images' => $images,
                'staffList' => $this->staffList
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

    public function exportPdf()
    {
        $user = $this->getUser();
        if (($user['role'] ?? 'staff') !== 'admin') {
            $_SESSION['error'] = 'Chỉ quản trị viên mới có quyền xuất PDF.';
            $this->redirect('/Quan_ly_trung_tam/public/completion-slips');
            return;
        }

        $filters = $this->buildFilters($_GET);

        try {
            $slips = $this->completionSlipModel->getAllWithRelations($filters);
            $attachments = $this->collectAttachmentFiles($slips);

            if (empty($attachments)) {
                throw new Exception('Không tìm thấy ảnh phiếu nào phù hợp với bộ lọc đã chọn để xuất PDF.');
            }

            $autoloadPath = defined('BASE_PATH') ? BASE_PATH . '/vendor/autoload.php' : dirname(__DIR__, 2) . '/vendor/autoload.php';
            if (file_exists($autoloadPath)) {
                require_once $autoloadPath;
            }

            $pdf = new \setasign\Fpdi\Tcpdf\Fpdi('P', 'mm', 'A4', true, 'UTF-8');
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(10, 10, 10);
            $pdf->SetAutoPageBreak(false);
            $pdf->SetFont('dejavusans', '', 11, '', true);
            $pdf->SetCreator('Completion Slip Export');
            $pdf->SetTitle('Completion Slip Attachments');

            $totalAttachments = count($attachments);
            foreach ($attachments as $index => $attachment) {
                $extension = $attachment['extension'];
                $slip = $attachment['slip'];
                $fileName = $attachment['name'];

                if ($extension === 'pdf') {
                    $pageCount = $pdf->setSourceFile($attachment['path']);
                    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                        $templateId = $pdf->importPage($pageNo);
                        $templateSize = $pdf->getTemplateSize($templateId);
                        $orientation = $templateSize['width'] >= $templateSize['height'] ? 'L' : 'P';
                        $pdf->AddPage($orientation, [$templateSize['width'], $templateSize['height']]);
                        $contentTop = $this->renderPdfHeader($pdf, $slip, $fileName, $index + 1, $totalAttachments, $pageNo, $pageCount);
                        $contentTop += 2;

                        $availableWidth = $pdf->getPageWidth() - 20;
                        $availableHeight = $pdf->getPageHeight() - $contentTop - 10;
                        if ($availableWidth <= 0 || $availableHeight <= 0) {
                            continue;
                        }

                        $scale = min($availableWidth / $templateSize['width'], $availableHeight / $templateSize['height'], 1);
                        $renderWidth = $templateSize['width'] * $scale;
                        $renderHeight = $templateSize['height'] * $scale;
                        $offsetX = ($pdf->getPageWidth() - $renderWidth) / 2;

                        $pdf->useTemplate($templateId, $offsetX, $contentTop, $renderWidth, $renderHeight);
                    }
                } else {
                    $imageInfo = @getimagesize($attachment['path']);
                    if (!$imageInfo) {
                        continue;
                    }

                    $orientation = ($imageInfo[0] >= $imageInfo[1]) ? 'L' : 'P';
                    $pdf->AddPage($orientation);
                    $contentTop = $this->renderPdfHeader($pdf, $slip, $fileName, $index + 1, $totalAttachments, 1, 1);
                    $contentTop += 2;

                    $availableWidth = $pdf->getPageWidth() - 20;
                    $availableHeight = $pdf->getPageHeight() - $contentTop - 10;
                    if ($availableWidth <= 0 || $availableHeight <= 0) {
                        continue;
                    }

                    $scale = min($availableWidth / $imageInfo[0], $availableHeight / $imageInfo[1]);
                    $renderWidth = $imageInfo[0] * $scale;
                    $renderHeight = $imageInfo[1] * $scale;
                    $offsetX = ($pdf->getPageWidth() - $renderWidth) / 2;

                    $pdf->Image($attachment['path'], $offsetX, $contentTop, $renderWidth, $renderHeight);
                }
            }

            $fileLabelParts = [];
            if (!empty($filters['date_from'])) {
                $fromTs = strtotime($filters['date_from']);
                if ($fromTs) {
                    $fileLabelParts[] = 'from-' . date('Ymd', $fromTs);
                }
            }
            if (!empty($filters['date_to'])) {
                $toTs = strtotime($filters['date_to']);
                if ($toTs) {
                    $fileLabelParts[] = 'to-' . date('Ymd', $toTs);
                }
            }
            $fileLabel = !empty($fileLabelParts) ? implode('-', $fileLabelParts) . '-' : '';
            $fileName = 'completion-slips-' . $fileLabel . date('Ymd_His') . '.pdf';

            if (ob_get_length()) {
                ob_end_clean();
            }

            $pdf->Output($fileName, 'D');
            exit;
        } catch (Exception $e) {
            error_log('CompletionSlip exportPdf error: ' . $e->getMessage());
            $_SESSION['error'] = $e->getMessage();
            $this->redirect('/Quan_ly_trung_tam/public/completion-slips');
        }
    }

    private function buildFilters(array $input): array
    {
        $filters = [];

        if (!empty($input['course_id'])) {
            $filters['course_id'] = (int) $input['course_id'];
        }

        if (!empty($input['search'])) {
            $filters['search'] = trim($input['search']);
        }

        if (!empty($input['teacher'])) {
            $filters['teacher'] = trim($input['teacher']);
        }

        if (!empty($input['date_from'])) {
            $filters['date_from'] = $input['date_from'];
        }

        if (!empty($input['date_to'])) {
            $filters['date_to'] = $input['date_to'];
        }

        if (!empty($input['created_by'])) {
            $filters['created_by'] = (int) $input['created_by'];
        }

        $allowedSorts = ['newest', 'oldest', 'student_asc', 'student_desc'];
        $sort = $input['sort'] ?? 'newest';
        $filters['sort'] = in_array($sort, $allowedSorts, true) ? $sort : 'newest';

        return $filters;
    }

    private function collectAttachmentFiles(array $slips): array
    {
        $files = [];

        foreach ($slips as $slip) {
            if (empty($slip['image_files'])) {
                continue;
            }

            $imageList = json_decode($slip['image_files'], true);
            if (!is_array($imageList)) {
                continue;
            }

            foreach ($imageList as $fileName) {
                $path = $this->getUploadAbsolutePath($fileName);
                if (!$path || !is_file($path)) {
                    continue;
                }

                $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                if (!in_array($extension, ['jpg', 'jpeg', 'png', 'pdf'], true)) {
                    continue;
                }

                $files[] = [
                    'path' => $path,
                    'name' => $fileName,
                    'extension' => $extension,
                    'slip' => $slip
                ];
            }
        }

        return $files;
    }

    private function getUploadAbsolutePath(?string $fileName): ?string
    {
        if (empty($fileName)) {
            return null;
        }

        $basePath = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2);
        return $basePath . '/public/uploads/' . $fileName;
    }

    private function renderPdfHeader($pdf, array $slip, string $fileName, int $attachmentIndex, int $attachmentTotal, int $pageNo, int $pageCount): float
    {
        $pdf->SetXY(10, 10);
        $pdf->SetTextColor(33, 37, 41);
        $pdf->SetFont('dejavusans', 'B', 12, '', true);
        $pdf->Cell(0, 6, 'Phiếu ' . $attachmentIndex . '/' . $attachmentTotal, 0, 1);

        $pdf->SetFont('dejavusans', '', 11, '', true);
        $pdf->Cell(0, 6, 'Học viên: ' . ($slip['student_name'] ?? 'Không rõ'), 0, 1);
        $pdf->Cell(0, 6, 'Khóa học: ' . ($slip['course_name'] ?? 'Chưa xác định'), 0, 1);
        $pdf->Cell(0, 6, 'Giáo viên: ' . ($slip['teacher_name'] ?? 'Không rõ'), 0, 1);
        if (!empty($slip['created_at'])) {
            $createdAt = strtotime($slip['created_at']);
            if ($createdAt) {
                $pdf->Cell(0, 6, 'Ngày tạo: ' . date('d/m/Y H:i', $createdAt), 0, 1);
            }
        }

        $pdf->SetFont('dejavusans', 'I', 10, '', true);
        $pdf->Cell(0, 6, 'File: ' . $fileName . ' | Trang ' . $pageNo . '/' . $pageCount, 0, 1);
        $pdf->Ln(2);

        return $pdf->GetY();
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
