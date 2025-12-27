<?php

require_once __DIR__ . '/../helpers/AdminLogger.php';

use App\Helpers\AdminLogger;

class ActivityLogger
{
    private const MODULE_TABLE_MAP = [
        'revenue' => 'revenue_reports',
        'reports' => 'reports',
        'students' => 'students',
        'courses' => 'courses',
        'certificates' => 'certificates',
        'staff' => 'users',
        'users' => 'users',
        'admin-logs' => 'admin_logs'
    ];

    public static function logCurrentRequest(array $route, string $uri, string $requestMethod, array $parameters = []): void
    {
        if (!isset($_SESSION['user_id'])) {
            return;
        }

        try {
            $db = Database::getInstance()->getConnection();
        } catch (\Exception $e) {
            error_log('[ActivityLogger] Database unavailable: ' . $e->getMessage());
            return;
        }

        $logger = new AdminLogger($db, $_SESSION['user_id'], $_SESSION['username'] ?? null);

        $actionType = self::mapActionType($requestMethod, $route['uri'] ?? '');
        $module = self::detectModule($uri);
        $requestData = self::captureRequestData();

        $recordId = self::detectRecordId($parameters, $requestData);
        $oldData = self::fetchOldData($db, $module, $recordId);
        $newData = ($actionType === 'view') ? null : $requestData;

        $changeSummary = self::summarizeChanges($oldData, $newData);
        $description = self::buildDescription($actionType, $module, $recordId, $changeSummary, $uri);

        $logger->log($actionType, $module, $description, $requestData, $oldData, $newData);
    }

    private static function detectModule(string $uri): string
    {
        $segments = explode('/', trim($uri, '/'));

        if (empty($segments[0])) {
            return 'root';
        }

        if ($segments[0] === 'api' && isset($segments[1])) {
            return $segments[1];
        }

        return $segments[0];
    }

    private static function mapActionType(string $method, string $routeUri): string
    {
        $method = strtoupper($method);

        if ($method === 'GET') {
            return 'view';
        }

        if ($method === 'PUT') {
            return 'update';
        }

        if ($method === 'DELETE') {
            return 'delete';
        }

        if ($method === 'POST') {
            $routeLower = strtolower($routeUri);

            if (strpos($routeLower, 'delete') !== false) {
                return 'delete';
            }

            if (strpos($routeLower, 'update') !== false || strpos($routeLower, 'edit') !== false) {
                return 'update';
            }

            if (strpos($routeLower, 'export') !== false) {
                return 'export';
            }

            return 'create';
        }

        return 'other';
    }

    private static function buildDescription(string $actionType, string $module, ?int $recordId, ?string $changeSummary, string $uri): string
    {
        $moduleLabel = self::humanizeModule($module);

        $actionLabels = [
            'create' => 'Thêm',
            'update' => 'Sửa',
            'delete' => 'Xóa',
            'view'   => 'Xem',
            'export' => 'Xuất',
            'login'  => 'Đăng nhập',
            'logout' => 'Đăng xuất',
            'other'  => 'Thao tác'
        ];

        $actionText = $actionLabels[$actionType] ?? 'Thao tác';

        $target = $moduleLabel;
        if ($recordId) {
            $target .= " #{$recordId}";
        }

        if (!empty($changeSummary)) {
            return "$actionText $target: $changeSummary";
        }

        // Fallback khi không có tóm tắt thay đổi
        if (!empty($uri)) {
            return "$actionText $target (" . $uri . ")";
        }

        return "$actionText $target";
    }

    private static function humanizeModule(string $module): string
    {
        $map = [
            'revenue' => 'doanh thu',
            'reports' => 'báo cáo',
            'students' => 'học viên',
            'courses' => 'khóa học',
            'certificates' => 'chứng nhận',
            'staff' => 'nhân sự',
            'users' => 'người dùng',
            'admin-logs' => 'nhật ký',
            'auth' => 'đăng nhập'
        ];

        if (isset($map[$module])) {
            return $map[$module];
        }

        $readable = str_replace(['-', '_'], ' ', $module);
        return trim($readable) ?: 'mục';
    }

    private static function detectRecordId(array $parameters, array $requestData): ?int
    {
        foreach ($parameters as $param) {
            if (is_numeric($param)) {
                return (int) $param;
            }
        }

        if (isset($requestData['id']) && is_numeric($requestData['id'])) {
            return (int) $requestData['id'];
        }

        return null;
    }

    private static function fetchOldData($db, string $module, ?int $recordId): ?array
    {
        if (!$recordId) {
            return null;
        }

        $table = self::MODULE_TABLE_MAP[$module] ?? null;

        if (!$table) {
            return null;
        }

        try {
            $stmt = $db->prepare("SELECT * FROM {$table} WHERE id = ? LIMIT 1");
            $stmt->execute([$recordId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$row) {
                return null;
            }

            return self::sanitize($row);
        } catch (\Exception $e) {
            error_log('[ActivityLogger] fetchOldData error: ' . $e->getMessage());
            return null;
        }
    }

    private static function summarizeChanges(?array $oldData, ?array $newData): ?string
    {
        if (!$oldData || !$newData) {
            return null;
        }

        $interestingKeys = [
            'name', 'full_name', 'student_name', 'course_name', 'username', 'phone', 'email',
            'status', 'approval_status', 'receive_status', 'amount', 'notes', 'title'
        ];

        $labels = [
            'name' => 'tên',
            'full_name' => 'tên',
            'student_name' => 'tên học viên',
            'course_name' => 'tên khóa học',
            'username' => 'tên đăng nhập',
            'phone' => 'số điện thoại',
            'email' => 'email',
            'status' => 'trạng thái',
            'approval_status' => 'trạng thái duyệt',
            'receive_status' => 'trạng thái nhận',
            'amount' => 'số tiền',
            'notes' => 'ghi chú',
            'title' => 'tiêu đề'
        ];

        $changes = [];

        foreach ($interestingKeys as $key) {
            if (!array_key_exists($key, $oldData) || !array_key_exists($key, $newData)) {
                continue;
            }

            $oldVal = self::stringify($oldData[$key]);
            $newVal = self::stringify($newData[$key]);

            if ($oldVal === $newVal) {
                continue;
            }

            $label = $labels[$key] ?? $key;
            $changes[] = sprintf('Đổi %s từ "%s" sang "%s"', $label, $oldVal, $newVal);

            if (count($changes) >= 3) {
                break;
            }
        }

        if (empty($changes)) {
            return null;
        }

        return 'Thay đổi ' . implode(', ', $changes);
    }

    private static function stringify($value): string
    {
        if (is_array($value)) {
            return '[array]';
        }

        if (is_null($value)) {
            return 'null';
        }

        $string = (string) $value;

        return strlen($string) > 60 ? substr($string, 0, 57) . '...' : $string;
    }

    private static function captureRequestData(): array
    {
        $data = array_merge($_GET ?? [], $_POST ?? []);

        if (!empty($_FILES)) {
            $data['_files'] = self::summarizeFiles($_FILES);
        }

        return self::sanitize($data);
    }

    private static function summarizeFiles(array $files): array
    {
        $summary = [];

        foreach ($files as $key => $file) {
            if (isset($file['name'])) {
                $summary[$key] = [
                    'name' => $file['name'],
                    'size' => $file['size'] ?? null,
                    'type' => $file['type'] ?? null,
                    'error' => $file['error'] ?? null
                ];
            } else {
                $summary[$key] = '[complex file]';
            }
        }

        return $summary;
    }

    private static function sanitize($value)
    {
        $sensitiveKeys = [
            'password',
            'current_password',
            'new_password',
            'confirm_password',
            'password_confirmation',
            'token',
            '_token',
            'csrf_token'
        ];

        if (is_array($value)) {
            $clean = [];

            foreach ($value as $key => $item) {
                if (is_string($key) && in_array(strtolower($key), $sensitiveKeys, true)) {
                    $clean[$key] = '[masked]';
                    continue;
                }

                $clean[$key] = self::sanitize($item);
            }

            return $clean;
        }

        if (is_string($value) && strlen($value) > 1000) {
            return substr($value, 0, 1000) . '...';
        }

        return $value;
    }
}
