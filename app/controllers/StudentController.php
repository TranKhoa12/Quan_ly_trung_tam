<?php

class StudentController extends BaseController
{
    private $studentModel;
    private $courseModel;
    private $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->studentModel = new Student();
        $this->courseModel = new Course();
        $this->userModel = new User();
        $this->requireAuth(); // Yêu cầu đăng nhập
        $this->requireAdmin(); // Chỉ admin mới được quản lý học viên
    }

    public function index()
    {
        try {
            $students = $this->studentModel->getStudentsWithDetails();
            $this->view('students/index', ['students' => $students]);
        } catch (Exception $e) {
            $this->view('students/index', ['students' => [], 'error' => $e->getMessage()]);
        }
    }

    public function create()
    {
        try {
            $courses = $this->courseModel->getActiveCourses();
            $instructors = $this->userModel->getStaffList();
            $this->view('students/create', [
                'courses' => $courses,
                'instructors' => $instructors
            ]);
        } catch (Exception $e) {
            $this->view('students/create', [
                'courses' => [],
                'instructors' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    public function store()
    {
        try {
            $data = [
                'full_name' => $_POST['full_name'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'email' => $_POST['email'] ?? '',
                'course_id' => !empty($_POST['course_id']) ? $_POST['course_id'] : null,
                'instructor_id' => !empty($_POST['instructor_id']) ? $_POST['instructor_id'] : null,
                'enrollment_date' => $_POST['enrollment_date'] ?? null,
                'completion_date' => $_POST['completion_date'] ?? null,
                'status' => $_POST['status'] ?? 'studying'
            ];

            // Handle file upload for tracking image
            if (isset($_FILES['tracking_image']) && $_FILES['tracking_image']['error'] === UPLOAD_ERR_OK) {
                try {
                    $fileName = $this->uploadFile($_FILES['tracking_image'], ['jpg', 'jpeg', 'png', 'pdf']);
                    $data['tracking_image'] = $fileName;
                } catch (Exception $e) {
                    throw new Exception('Lỗi upload file: ' . $e->getMessage());
                }
            }

            $studentId = $this->studentModel->create($data);

            header('Location: /Quan_ly_trung_tam/public/students');
            exit;
        } catch (Exception $e) {
            $courses = $this->courseModel->getActiveCourses();
            $instructors = $this->userModel->getStaffList();
            $this->view('students/create', [
                'courses' => $courses,
                'instructors' => $instructors,
                'error' => $e->getMessage(),
                'old_data' => $_POST
            ]);
        }
    }

    // API Methods
    public function apiIndex()
    {
        try {
            $students = $this->studentModel->getStudentsWithDetails();
            $this->json(['success' => true, 'data' => $students]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function apiStore()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            $data = [
                'full_name' => $input['full_name'] ?? '',
                'phone' => $input['phone'] ?? '',
                'email' => $input['email'] ?? '',
                'course_id' => !empty($input['course_id']) ? $input['course_id'] : null,
                'instructor_id' => !empty($input['instructor_id']) ? $input['instructor_id'] : null,
                'enrollment_date' => $input['enrollment_date'] ?? null,
                'completion_date' => $input['completion_date'] ?? null,
                'status' => $input['status'] ?? 'studying'
            ];

            $studentId = $this->studentModel->create($data);

            $this->json(['success' => true, 'data' => ['id' => $studentId]]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function apiUpdate($id)
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            $data = [
                'full_name' => $input['full_name'],
                'phone' => $input['phone'],
                'email' => $input['email'],
                'course_id' => !empty($input['course_id']) ? $input['course_id'] : null,
                'instructor_id' => !empty($input['instructor_id']) ? $input['instructor_id'] : null,
                'enrollment_date' => $input['enrollment_date'],
                'completion_date' => $input['completion_date'],
                'status' => $input['status']
            ];

            $updated = $this->studentModel->update($id, $data);
            
            if (!$updated) {
                $this->json(['success' => false, 'message' => 'Không thể cập nhật học viên'], 400);
                return;
            }

            $this->json(['success' => true, 'message' => 'Cập nhật thành công']);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}