<?php

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

    protected function uploadFile($file, $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf'])
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
        
        // Get correct path to uploads directory
        // Use BASE_PATH constant if available, fallback to calculated path
        if (defined('BASE_PATH')) {
            $uploadDir = BASE_PATH . '/public/uploads/';
        } else {
            $uploadDir = dirname(__DIR__) . '/public/uploads/';
        }
        
        // Ensure upload directory exists and is writable
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

    protected function uploadFileWithCustomName($file, $customFileName, $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf'])
    {
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload error');
        }

        $fileInfo = pathinfo($file['name']);
        $extension = strtolower($fileInfo['extension']);

        if (!in_array($extension, $allowedTypes)) {
            throw new Exception('File type not allowed');
        }

        // Use custom filename
        $fileName = $customFileName;
        
        // Get correct path to uploads directory
        if (defined('BASE_PATH')) {
            $uploadDir = BASE_PATH . '/public/uploads/';
        } else {
            $uploadDir = dirname(__DIR__) . '/public/uploads/';
        }
        
        // Ensure upload directory exists and is writable
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
}