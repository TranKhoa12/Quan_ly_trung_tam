<?php

require_once __DIR__ . '/../helpers/AdminLogger.php';

use App\Helpers\AdminLogger;

class AdminLogController extends BaseController
{
    public function __construct()
    {
        $this->requireAuth();
        $this->requireAdmin(); // Chỉ admin mới được xem logs
        $this->db = Database::getInstance();
    }

    /**
     * Hiển thị trang danh sách logs
     */
    public function index()
    {
        // Lấy filters từ query parameters
        $filters = [
            'user_id' => $_GET['user_id'] ?? null,
            'action_type' => $_GET['action_type'] ?? null,
            'module' => $_GET['module'] ?? null,
            'from_date' => $_GET['from_date'] ?? date('Y-m-d', strtotime('-30 days')),
            'to_date' => $_GET['to_date'] ?? date('Y-m-d'),
            'search' => $_GET['search'] ?? null
        ];

        // Pagination
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 50;
        $offset = ($page - 1) * $limit;

        // Lấy logs
        $logs = AdminLogger::getLogs($this->db->getConnection(), $filters, $limit, $offset);
        $totalLogs = AdminLogger::countLogs($this->db->getConnection(), $filters);
        $totalPages = ceil($totalLogs / $limit);

        // Lấy danh sách users cho filter
        $users = $this->db->fetchAll("SELECT id, username, full_name FROM users ORDER BY username");

        // Lấy danh sách modules duy nhất
        $modules = $this->db->fetchAll("SELECT DISTINCT module FROM admin_logs ORDER BY module");

        $this->view('admin-logs/index', [
            'logs' => $logs,
            'filters' => $filters,
            'users' => $users,
            'modules' => $modules,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalLogs' => $totalLogs
        ]);
    }

    /**
     * Xem chi tiết một log
     */
    public function detail($logId)
    {
        $log = AdminLogger::getLogDetail($this->db->getConnection(), $logId);
        
        if (!$log) {
            $_SESSION['error'] = 'Không tìm thấy log';
            $this->redirect('/Quan_ly_trung_tam/public/admin-logs');
        }

        // Parse JSON data
        $log['request_data_decoded'] = $log['request_data'] ? json_decode($log['request_data'], true) : null;
        $log['old_data_decoded'] = $log['old_data'] ? json_decode($log['old_data'], true) : null;
        $log['new_data_decoded'] = $log['new_data'] ? json_decode($log['new_data'], true) : null;

        $this->view('admin-logs/detail', ['log' => $log]);
    }

    /**
     * Xuất logs ra file CSV
     */
    public function export()
    {
        // Lấy filters từ query parameters
        $filters = [
            'user_id' => $_GET['user_id'] ?? null,
            'action_type' => $_GET['action_type'] ?? null,
            'module' => $_GET['module'] ?? null,
            'from_date' => $_GET['from_date'] ?? null,
            'to_date' => $_GET['to_date'] ?? null,
            'search' => $_GET['search'] ?? null
        ];

        // Lấy tất cả logs theo filter (không giới hạn)
        $logs = AdminLogger::getLogs($this->db->getConnection(), $filters, 10000, 0);

        // Ghi log xuất file
        $logger = new AdminLogger($this->db->getConnection(), $_SESSION['user_id'], $_SESSION['username']);
        $logger->logExport('admin_logs', 'Xuất danh sách logs ra file CSV', $filters);

        // Tạo CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="admin-logs-' . date('Y-m-d-His') . '.csv"');
        
        // Thêm BOM để Excel hiển thị đúng tiếng Việt
        echo "\xEF\xBB\xBF";
        
        $output = fopen('php://output', 'w');
        
        // Header
        fputcsv($output, [
            'ID',
            'Thời gian',
            'Nhân viên',
            'Loại hành động',
            'Module',
            'Mô tả',
            'IP Address'
        ]);

        // Data
        foreach ($logs as $log) {
            fputcsv($output, [
                $log['id'],
                $log['created_at'],
                $log['username'],
                $log['action_type'],
                $log['module'],
                $log['description'],
                $log['ip_address']
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Xóa logs cũ (chỉ admin mới được xóa)
     */
    public function deleteOld()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/Quan_ly_trung_tam/public/admin-logs');
        }

        $days = intval($_POST['days'] ?? 90);
        
        if ($days < 30) {
            $_SESSION['error'] = 'Chỉ có thể xóa logs cũ hơn 30 ngày';
            $this->redirect('/Quan_ly_trung_tam/public/admin-logs');
        }

        $dateThreshold = date('Y-m-d', strtotime("-{$days} days"));
        
        $sql = "DELETE FROM admin_logs WHERE DATE(created_at) < ?";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([$dateThreshold]);
        
        $deletedCount = $stmt->rowCount();

        // Ghi log xóa
        $logger = new AdminLogger($this->db->getConnection(), $_SESSION['user_id'], $_SESSION['username']);
        $logger->logDelete('admin_logs', "Xóa {$deletedCount} logs cũ hơn {$days} ngày");

        $_SESSION['success'] = "Đã xóa {$deletedCount} logs cũ hơn {$days} ngày";
        $this->redirect('/Quan_ly_trung_tam/public/admin-logs');
    }

    /**
     * API để lấy thống kê logs (dùng cho chart)
     */
    public function statistics()
    {
        $days = intval($_GET['days'] ?? 30);
        
        // Thống kê theo ngày
        $sql = "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as total,
                    SUM(CASE WHEN action_type = 'login' THEN 1 ELSE 0 END) as login_count,
                    SUM(CASE WHEN action_type = 'create' THEN 1 ELSE 0 END) as create_count,
                    SUM(CASE WHEN action_type = 'update' THEN 1 ELSE 0 END) as update_count,
                    SUM(CASE WHEN action_type = 'delete' THEN 1 ELSE 0 END) as delete_count
                FROM admin_logs 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(created_at)
                ORDER BY date DESC";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([$days]);
        $dailyStats = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Thống kê theo user
        $sql = "SELECT 
                    username,
                    COUNT(*) as total_actions,
                    SUM(CASE WHEN action_type = 'login' THEN 1 ELSE 0 END) as login_count
                FROM admin_logs 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY user_id, username
                ORDER BY total_actions DESC
                LIMIT 10";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([$days]);
        $userStats = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'daily' => $dailyStats,
            'users' => $userStats
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
