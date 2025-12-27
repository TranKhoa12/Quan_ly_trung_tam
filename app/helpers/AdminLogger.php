<?php

namespace App\Helpers;

/**
 * Class AdminLogger
 * Ghi lại các hoạt động của nhân viên trong hệ thống
 */
class AdminLogger
{
    private $db;
    private $userId;
    private $username;

    public function __construct($db, $userId = null, $username = null)
    {
        $this->db = $db;
        $this->userId = $userId;
        $this->username = $username;
    }

    /**
     * Ghi log đăng nhập
     * @param bool $success - True nếu đăng nhập thành công, False nếu thất bại
     * @return bool
     */
    public function logLogin($success = true)
    {
        $description = $success 
            ? "Đăng nhập thành công" 
            : "Đăng nhập thất bại";
        
        return $this->log('login', 'auth', $description);
    }

    /**
     * Ghi log đăng xuất
     * @return bool
     */
    public function logLogout()
    {
        return $this->log('logout', 'auth', 'Đăng xuất');
    }

    /**
     * Ghi log tạo mới
     * @param string $module - Tên module (students, courses, teachers...)
     * @param string $description - Mô tả hành động
     * @param array|null $newData - Dữ liệu mới được tạo
     * @return bool
     */
    public function logCreate($module, $description, $newData = null)
    {
        return $this->log('create', $module, $description, null, null, $newData);
    }

    /**
     * Ghi log cập nhật
     * @param string $module - Tên module
     * @param string $description - Mô tả hành động
     * @param array|null $oldData - Dữ liệu cũ trước khi cập nhật
     * @param array|null $newData - Dữ liệu mới sau khi cập nhật
     * @return bool
     */
    public function logUpdate($module, $description, $oldData = null, $newData = null)
    {
        return $this->log('update', $module, $description, null, $oldData, $newData);
    }

    /**
     * Ghi log xóa
     * @param string $module - Tên module
     * @param string $description - Mô tả hành động
     * @param array|null $oldData - Dữ liệu bị xóa
     * @return bool
     */
    public function logDelete($module, $description, $oldData = null)
    {
        return $this->log('delete', $module, $description, null, $oldData);
    }

    /**
     * Ghi log xem/truy cập
     * @param string $module - Tên module
     * @param string $description - Mô tả hành động
     * @param array|null $requestData - Dữ liệu request
     * @return bool
     */
    public function logView($module, $description, $requestData = null)
    {
        return $this->log('view', $module, $description, $requestData);
    }

    /**
     * Ghi log xuất file
     * @param string $module - Tên module
     * @param string $description - Mô tả hành động
     * @param array|null $requestData - Dữ liệu request
     * @return bool
     */
    public function logExport($module, $description, $requestData = null)
    {
        return $this->log('export', $module, $description, $requestData);
    }

    /**
     * Ghi log chung
     * @param string $actionType - Loại hành động (login, logout, create, update, delete, view, export, other)
     * @param string $module - Tên module
     * @param string $description - Mô tả hành động
     * @param array|null $requestData - Dữ liệu request
     * @param array|null $oldData - Dữ liệu cũ
     * @param array|null $newData - Dữ liệu mới
     * @return bool
     */
    public function log(
        $actionType, 
        $module, 
        $description, 
        $requestData = null, 
        $oldData = null, 
        $newData = null
    ) {
        try {
            $sql = "INSERT INTO admin_logs (
                user_id, username, action_type, module, description,
                ip_address, user_agent, request_data, old_data, new_data
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->db->prepare($sql);
            
            $ipAddress = $this->getClientIp();
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $requestDataJson = $requestData ? json_encode($requestData, JSON_UNESCAPED_UNICODE) : null;
            $oldDataJson = $oldData ? json_encode($oldData, JSON_UNESCAPED_UNICODE) : null;
            $newDataJson = $newData ? json_encode($newData, JSON_UNESCAPED_UNICODE) : null;

            // PDO binding
            $stmt->execute([
                $this->userId,
                $this->username,
                $actionType,
                $module,
                $description,
                $ipAddress,
                $userAgent,
                $requestDataJson,
                $oldDataJson,
                $newDataJson
            ]);

            return true;
        } catch (\Exception $e) {
            // Log lỗi nhưng không throw exception để không làm gián đoạn luồng chính
            error_log("AdminLogger Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy IP thực của client (xử lý proxy và CDN)
     * @return string
     */
    private function getClientIp()
    {
        $ipKeys = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',  // Proxy
            'HTTP_X_REAL_IP',         // Nginx
            'REMOTE_ADDR'             // Direct connection
        ];

        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                // Nếu có nhiều IP (qua nhiều proxy), lấy IP đầu tiên
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                return trim($ip);
            }
        }

        return '0.0.0.0';
    }

    /**
     * Lấy danh sách logs theo điều kiện
     * @param \PDO $db - Database connection (PDO)
     * @param array $filters - Các điều kiện lọc
     * @param int $limit - Số lượng bản ghi tối đa
     * @param int $offset - Vị trí bắt đầu
     * @return array
     */
    public static function getLogs($db, $filters = [], $limit = 100, $offset = 0)
    {
        $where = [];
        $params = [];

        if (!empty($filters['user_id'])) {
            $where[] = "user_id = ?";
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['action_type'])) {
            $where[] = "action_type = ?";
            $params[] = $filters['action_type'];
        }

        if (!empty($filters['module'])) {
            $where[] = "module = ?";
            $params[] = $filters['module'];
        }

        if (!empty($filters['from_date'])) {
            $where[] = "DATE(created_at) >= ?";
            $params[] = $filters['from_date'];
        }

        if (!empty($filters['to_date'])) {
            $where[] = "DATE(created_at) <= ?";
            $params[] = $filters['to_date'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(description LIKE ? OR username LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT * FROM admin_logs 
                {$whereClause} 
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Đếm tổng số logs theo điều kiện
     * @param \PDO $db - Database connection (PDO)
     * @param array $filters - Các điều kiện lọc
     * @return int
     */
    public static function countLogs($db, $filters = [])
    {
        $where = [];
        $params = [];

        if (!empty($filters['user_id'])) {
            $where[] = "user_id = ?";
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['action_type'])) {
            $where[] = "action_type = ?";
            $params[] = $filters['action_type'];
        }

        if (!empty($filters['module'])) {
            $where[] = "module = ?";
            $params[] = $filters['module'];
        }

        if (!empty($filters['from_date'])) {
            $where[] = "DATE(created_at) >= ?";
            $params[] = $filters['from_date'];
        }

        if (!empty($filters['to_date'])) {
            $where[] = "DATE(created_at) <= ?";
            $params[] = $filters['to_date'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(description LIKE ? OR username LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT COUNT(*) as total FROM admin_logs {$whereClause}";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int) $result['total'];
    }

    /**
     * Lấy chi tiết một log record
     * @param \PDO $db - Database connection (PDO)
     * @param int $logId - ID của log
     * @return array|null
     */
    public static function getLogDetail($db, $logId)
    {
        $sql = "SELECT * FROM admin_logs WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$logId]);
        
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}
