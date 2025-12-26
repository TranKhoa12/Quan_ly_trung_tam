<?php

// Load view helpers
require_once __DIR__ . '/../app/helpers/view_helpers.php';

class BaseController
{
    protected $db;
    
    public function __construct()
    {
        try {
            $this->db = Database::getInstance();
        } catch (Exception $e) {
            // Gracefully handle database connection failures so pages still render
            $this->db = null;
            error_log('[BaseController] Database unavailable: ' . $e->getMessage());
        }
        // Remove automatic checkAuth() call - let child controllers decide
    }

    protected function checkAuth()
    {
        // Override in child controllers if authentication is required
    }

    protected function view($viewName, $data = [])
    {
        extract($data);
        $viewPath = __DIR__ . '/../app/views/' . $viewName . '.php';
        
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            throw new Exception("View not found: $viewName at path: $viewPath");
        }
    }

    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function redirect($url)
    {
        header("Location: $url");
        exit;
    }

    protected function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    protected function requireAuth()
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('/Quan_ly_trung_tam/public/login');
        }
    }

    protected function getUser()
    {
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'full_name' => $_SESSION['full_name'],
                'role' => $_SESSION['role']
            ];
        }
        return null;
    }

    protected function requireRole($role)
    {
        $this->requireAuth();
        $user = $this->getUser();
        if ($user['role'] !== $role) {
            $this->json(['error' => 'Insufficient permissions'], 403);
        }
    }

    protected function requireAdmin()
    {
        $this->requireAuth();
        $user = $this->getUser();
        if ($user['role'] !== 'admin') {
            // Redirect non-admin users to dashboard instead of showing error
            $this->redirect('/Quan_ly_trung_tam/public/dashboard?error=access_denied');
        }
    }

    protected function validateInput($data, $rules)
    {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            if (isset($rule['required']) && $rule['required'] && empty($data[$field])) {
                $errors[$field] = $rule['message'] ?? "Field $field is required";
                continue;
            }
            
            if (!empty($data[$field])) {
                if (isset($rule['min']) && strlen($data[$field]) < $rule['min']) {
                    $errors[$field] = "Field $field must be at least {$rule['min']} characters";
                }
                
                if (isset($rule['max']) && strlen($data[$field]) > $rule['max']) {
                    $errors[$field] = "Field $field must not exceed {$rule['max']} characters";
                }
                
                if (isset($rule['email']) && $rule['email'] && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = "Field $field must be a valid email";
                }
            }
        }
        
        return $errors;
    }

    /**
     * Upload file lên Cloudinary (nếu được config) hoặc local storage
     * 
     * @param array $file - $_FILES array element
     * @param array $allowedTypes - Các loại file được phép
     * @param bool $useCloudinary - Force sử dụng Cloudinary (default: auto-detect từ config)
     * @return string - Trả về file name (local) hoặc JSON string (Cloudinary)
     */
    protected function uploadFile($file, $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf'], $useCloudinary = null)
    {
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload error');
        }

        $fileInfo = pathinfo($file['name']);
        $extension = strtolower($fileInfo['extension']);

        if (!in_array($extension, $allowedTypes)) {
            throw new Exception('File type not allowed');
        }

        $fileName = uniqid() . '.' . $extension;

        // Kiểm tra có dùng Cloudinary không
        $shouldUseCloudinary = $useCloudinary ?? $this->shouldUseCloudinary();

        if ($shouldUseCloudinary) {
            return $this->uploadToCloudinary($file, $fileName);
        }
        
        // Upload local
        if (defined('BASE_PATH')) {
            $uploadDir = BASE_PATH . '/public/uploads/';
        } else {
            $uploadDir = dirname(__DIR__) . '/public/uploads/';
        }
        
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                throw new Exception('Cannot create upload directory: ' . $uploadDir);
            }
        }
        
        if (!is_writable($uploadDir)) {
            throw new Exception('Upload directory is not writable: ' . $uploadDir);
        }
        
        $uploadPath = $uploadDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            throw new Exception('Failed to move uploaded file. Upload path: ' . $uploadPath);
        }

        return $fileName;
    }

    /**
     * Upload file với tên tùy chỉnh (local hoặc Cloudinary)
     */
    protected function uploadFileWithCustomName($file, $customFileName, $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf'], $useCloudinary = null)
    {
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload error');
        }

        $fileInfo = pathinfo($file['name']);
        $extension = strtolower($fileInfo['extension']);

        if (!in_array($extension, $allowedTypes)) {
            throw new Exception('File type not allowed');
        }

        $fileName = $customFileName;
        $shouldUseCloudinary = $useCloudinary ?? $this->shouldUseCloudinary();

        if ($shouldUseCloudinary) {
            return $this->uploadToCloudinary($file, $fileName);
        }
        
        // Upload local
        if (defined('BASE_PATH')) {
            $uploadDir = BASE_PATH . '/public/uploads/';
        } else {
            $uploadDir = dirname(__DIR__) . '/public/uploads/';
        }
        
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                throw new Exception('Cannot create upload directory: ' . $uploadDir);
            }
        }
        
        if (!is_writable($uploadDir)) {
            throw new Exception('Upload directory is not writable: ' . $uploadDir);
        }
        
        $uploadPath = $uploadDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            throw new Exception('Failed to move uploaded file. Upload path: ' . $uploadPath);
        }

        return $fileName;
    }

    /**
     * Kiểm tra có nên sử dụng Cloudinary không
     */
    protected function shouldUseCloudinary()
    {
        $configPath = __DIR__ . '/../config/cloudinary.php';
        if (file_exists($configPath)) {
            $config = require $configPath;
            return isset($config['enabled']) && $config['enabled'] === true;
        }
        return false;
    }

    /**
     * Upload file lên Cloudinary
     * 
     * @param array $file - $_FILES array element
     * @param string $fileName - Tên file
     * @return string - JSON string: {"id":"xxx","url":"xxx","name":"xxx"}
     */
    protected function uploadToCloudinary($file, $fileName)
    {
        require_once __DIR__ . '/../app/helpers/CloudinaryStorage.php';
        
        try {
            $cloudinary = new CloudinaryStorage();
            $result = $cloudinary->uploadFromUpload($file, $fileName);
            
            // Trả về dạng JSON string để lưu vào database
            return json_encode($result);
        } catch (Exception $e) {
            error_log('Cloudinary upload failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Xóa file (từ Cloudinary hoặc local)
     * 
     * @param string $fileInfo - Tên file (local) hoặc JSON string (Cloudinary)
     * @return bool
     */
    protected function deleteUploadedFile($fileInfo)
    {
        // Kiểm tra nếu là Cloudinary (JSON format)
        if ($this->isCloudinaryFile($fileInfo)) {
            return $this->deleteFromCloudinary($fileInfo);
        }

        // Xóa file local
        if (defined('BASE_PATH')) {
            $uploadDir = BASE_PATH . '/public/uploads/';
        } else {
            $uploadDir = dirname(__DIR__) . '/public/uploads/';
        }
        
        $filePath = $uploadDir . $fileInfo;
        
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        
        return false;
    }

    /**
     * Kiểm tra xem file có phải từ Cloudinary không
     */
    protected function isCloudinaryFile($fileInfo)
    {
        if (empty($fileInfo)) {
            return false;
        }
        
        // Kiểm tra nếu là JSON và có trường 'id' và 'url'
        $decoded = json_decode($fileInfo, true);
        return $decoded && isset($decoded['id']) && isset($decoded['url']);
    }

    /**
     * Xóa file từ Cloudinary
     */
    protected function deleteFromCloudinary($fileInfo)
    {
        require_once __DIR__ . '/../app/helpers/CloudinaryStorage.php';
        
        try {
            $decoded = json_decode($fileInfo, true);
            if (!$decoded || !isset($decoded['id'])) {
                return false;
            }

            $cloudinary = new CloudinaryStorage();
            return $cloudinary->deleteFile($decoded['id']);
        } catch (Exception $e) {
            error_log('Cloudinary delete failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy URL hiển thị file (Cloudinary hoặc local)
     * 
     * @param string $fileInfo - Tên file (local) hoặc JSON string (Cloudinary)
     * @return string
     */
    protected function getFileUrl($fileInfo)
    {
        if (empty($fileInfo)) {
            return '';
        }

        // Nếu là Cloudinary file
        if ($this->isCloudinaryFile($fileInfo)) {
            $decoded = json_decode($fileInfo, true);
            return $decoded['url'] ?? '';
        }

        // Local file
        return '/Quan_ly_trung_tam/public/uploads/' . $fileInfo;
    }
}