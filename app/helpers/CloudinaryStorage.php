<?php

use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;

/**
 * Cloudinary Storage Helper
 * Xử lý upload, delete và lấy URL từ Cloudinary
 */
class CloudinaryStorage
{
    private $uploadApi;
    private $folder;

    public function __construct($folder = 'quan-ly-trung-tam')
    {
        $this->initializeCloudinary();
        $this->uploadApi = new UploadApi();
        $this->folder = $folder;
    }

    /**
     * Khởi tạo Cloudinary với config
     */
    private function initializeCloudinary()
    {
        $configPath = __DIR__ . '/../../config/cloudinary.php';
        
        if (!file_exists($configPath)) {
            throw new Exception('Cloudinary config file not found at: ' . $configPath);
        }

        $config = require $configPath;

        if (empty($config['cloud_name']) || empty($config['api_key']) || empty($config['api_secret'])) {
            throw new Exception('Cloudinary credentials not configured properly');
        }

        Configuration::instance([
            'cloud' => [
                'cloud_name' => $config['cloud_name'],
                'api_key' => $config['api_key'],
                'api_secret' => $config['api_secret']
            ],
            'url' => [
                'secure' => true
            ]
        ]);
    }

    /**
     * Upload file lên Cloudinary
     * 
     * @param string $filePath - Đường dẫn file tạm thời
     * @param string $fileName - Tên file muốn lưu (không bắt buộc)
     * @param array $options - Tùy chọn upload
     * @return array ['id' => public_id, 'url' => secure_url, 'name' => original_name]
     */
    public function uploadFile($filePath, $fileName = null, $options = [])
    {
        try {
            if (!file_exists($filePath)) {
                throw new Exception('File not found: ' . $filePath);
            }

            $defaultOptions = [
                'folder' => $this->folder,
                'resource_type' => 'auto',
                'use_filename' => true,
                'unique_filename' => true
            ];

            if ($fileName) {
                $defaultOptions['public_id'] = pathinfo($fileName, PATHINFO_FILENAME);
            }

            $uploadOptions = array_merge($defaultOptions, $options);
            $result = $this->uploadApi->upload($filePath, $uploadOptions);

            return [
                'id' => $result['public_id'],
                'url' => $result['secure_url'],
                'name' => $fileName ?: basename($filePath),
                'format' => $result['format'] ?? pathinfo($filePath, PATHINFO_EXTENSION),
                'size' => $result['bytes'] ?? filesize($filePath)
            ];

        } catch (Exception $e) {
            error_log('Cloudinary upload error: ' . $e->getMessage());
            throw new Exception('Failed to upload file to Cloudinary: ' . $e->getMessage());
        }
    }

    /**
     * Upload từ file upload ($_FILES)
     * 
     * @param array $fileArray - $_FILES['field_name']
     * @param string $customFileName - Tên file tùy chỉnh (optional)
     * @return array ['id' => public_id, 'url' => secure_url, 'name' => file_name]
     */
    public function uploadFromUpload($fileArray, $customFileName = null)
    {
        if (!isset($fileArray['tmp_name']) || $fileArray['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload error');
        }

        $fileName = $customFileName ?: $fileArray['name'];
        $tmpPath = $fileArray['tmp_name'];

        return $this->uploadFile($tmpPath, $fileName);
    }

    /**
     * Xóa file từ Cloudinary
     * 
     * @param string $publicId - Cloudinary public ID
     * @return bool
     */
    public function deleteFile($publicId)
    {
        // Thử xóa với các resource_type khác nhau
        $resourceTypes = ['image', 'raw', 'video'];
        
        foreach ($resourceTypes as $type) {
            try {
                $result = $this->uploadApi->destroy($publicId, [
                    'resource_type' => $type,
                    'invalidate' => true
                ]);
                
                if (isset($result['result']) && $result['result'] === 'ok') {
                    return true;
                }
                
                // Nếu không tìm thấy file với type này, thử type tiếp theo
                if (isset($result['result']) && $result['result'] === 'not found') {
                    continue;
                }
                
            } catch (Exception $e) {
                // Thử type tiếp theo
                continue;
            }
        }
        
        error_log('Cloudinary delete failed for all resource types: ' . $publicId);
        return false;
    }

    /**
     * Lấy URL của file
     * 
     * @param string $publicId - Cloudinary public ID
     * @param array $transformations - Transformation options (resize, crop, etc.)
     * @return string
     */
    public function getFileUrl($publicId, $transformations = [])
    {
        // Nếu đã là full URL, trả về luôn
        if (filter_var($publicId, FILTER_VALIDATE_URL)) {
            return $publicId;
        }

        $config = Configuration::instance();
        $cloudName = $config->cloud->cloudName;
        
        $transformString = '';
        if (!empty($transformations)) {
            $parts = [];
            foreach ($transformations as $key => $value) {
                $parts[] = "{$key}_{$value}";
            }
            $transformString = implode(',', $parts) . '/';
        }

        return "https://res.cloudinary.com/{$cloudName}/image/upload/{$transformString}{$publicId}";
    }

    /**
     * Upload nhiều files cùng lúc
     * 
     * @param array $files - Mảng các file paths hoặc $_FILES arrays
     * @return array - Mảng kết quả upload
     */
    public function uploadMultiple($files)
    {
        $results = [];
        
        foreach ($files as $file) {
            try {
                if (is_string($file)) {
                    // File path
                    $results[] = $this->uploadFile($file);
                } elseif (is_array($file) && isset($file['tmp_name'])) {
                    // $_FILES array
                    $results[] = $this->uploadFromUpload($file);
                }
            } catch (Exception $e) {
                error_log('Error uploading file: ' . $e->getMessage());
                $results[] = ['error' => $e->getMessage()];
            }
        }

        return $results;
    }

    /**
     * Lấy thông tin file từ Cloudinary
     * 
     * @param string $publicId - Public ID
     * @return array
     */
    public function getFileInfo($publicId)
    {
        try {
            $api = new \Cloudinary\Api\Admin\AdminApi();
            return $api->asset($publicId);
        } catch (Exception $e) {
            error_log('Cloudinary get file info error: ' . $e->getMessage());
            throw new Exception('Failed to get file info: ' . $e->getMessage());
        }
    }
}
