<?php

require_once __DIR__ . '/../models/ShiftRegistration.php';
require_once __DIR__ . '/../models/TeachingShift.php';

class StaffShiftStatsController extends BaseController
{
    private $shiftRegistrationModel;
    private $teachingShiftModel;

    public function __construct()
    {
        parent::__construct();
        $this->shiftRegistrationModel = new ShiftRegistration();
        $this->teachingShiftModel = new TeachingShift();
        $this->requireAuth();
    }

    /**
     * Hiển thị trang thống kê ca dạy của nhân viên
     */
    public function index()
    {
        $user = $this->getUser();
        $staffId = $user['id'];
        
        // Lấy tháng/năm từ query string hoặc dùng tháng hiện tại
        $month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
        $year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
        
        // Validate month/year
        if ($month < 1 || $month > 12) $month = (int)date('m');
        if ($year < 2020 || $year > 2030) $year = (int)date('Y');
        
        $periodStart = date('Y-m-01', strtotime("$year-$month-01"));
        $periodEnd = date('Y-m-t', strtotime("$year-$month-01"));
        
        try {
            // Lấy thống kê tổng quan
            $stats = $this->getStaffShiftStats($staffId, $periodStart, $periodEnd);
            
            // Lấy danh sách ca dạy chi tiết
            $shifts = $this->getStaffShifts($staffId, $periodStart, $periodEnd);
            
            // Lấy thống kê theo trạng thái
            $statusStats = $this->getStatusStats($staffId, $periodStart, $periodEnd);
            
            // Lấy biểu đồ theo ngày
            $dailyStats = $this->getDailyStats($staffId, $periodStart, $periodEnd);
            
            $this->view('staff_shifts/stats', [
                'stats' => $stats,
                'shifts' => $shifts,
                'statusStats' => $statusStats,
                'dailyStats' => $dailyStats,
                'month' => $month,
                'year' => $year,
                'periodStart' => $periodStart,
                'periodEnd' => $periodEnd,
                'userRole' => $user['role'],
                'staffName' => $user['full_name']
            ]);
            
        } catch (Exception $e) {
            error_log('StaffShiftStats error: ' . $e->getMessage());
            $this->view('staff_shifts/stats', [
                'error' => 'Không thể tải thống kê: ' . $e->getMessage(),
                'stats' => [
                    'total_shifts' => 0,
                    'total_hours' => 0,
                    'total_amount' => 0,
                    'approved_shifts' => 0,
                    'pending_shifts' => 0
                ],
                'shifts' => [],
                'statusStats' => [],
                'dailyStats' => [],
                'month' => $month,
                'year' => $year,
                'periodStart' => $periodStart,
                'periodEnd' => $periodEnd,
                'userRole' => $user['role'],
                'staffName' => $user['full_name']
            ]);
        }
    }

    /**
     * Lấy thống kê tổng quan (chỉ tính ca đến hôm nay)
     */
    private function getStaffShiftStats($staffId, $periodStart, $periodEnd)
    {
        $today = date('Y-m-d');
        
        // Thống kê ca đã dạy (chỉ đến hôm nay)
        $sql = "SELECT 
                    COUNT(*) as total_shifts,
                    COALESCE(SUM(hours), 0) as total_hours,
                    COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_shifts,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_shifts,
                    COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_shifts
                FROM shift_registrations sr
                WHERE sr.staff_id = ? 
                AND sr.shift_date BETWEEN ? AND ?
                AND sr.shift_date <= ?";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([$staffId, $periodStart, $periodEnd, $today]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Đếm ca sắp tới
        $sqlUpcoming = "SELECT COUNT(*) as upcoming_shifts
                        FROM shift_registrations
                        WHERE staff_id = ?
                        AND shift_date BETWEEN ? AND ?
                        AND shift_date > ?";
        
        $stmtUpcoming = $this->db->getConnection()->prepare($sqlUpcoming);
        $stmtUpcoming->execute([$staffId, $periodStart, $periodEnd, $today]);
        $upcoming = $stmtUpcoming->fetch(PDO::FETCH_ASSOC);
        
        $result['upcoming_shifts'] = $upcoming['upcoming_shifts'] ?? 0;
        
        return $result ?: [
            'total_shifts' => 0,
            'total_hours' => 0,
            'approved_shifts' => 0,
            'pending_shifts' => 0,
            'rejected_shifts' => 0,
            'upcoming_shifts' => 0
        ];
    }

    /**
     * Lấy danh sách ca dạy chi tiết (bao gồm cả ca sắp tới)
     */
    private function getStaffShifts($staffId, $periodStart, $periodEnd)
    {
        $today = date('Y-m-d');
        
        $sql = "SELECT 
                    sr.*,
                    ts.name as shift_name,
                    ts.start_time,
                    ts.end_time,
                    ts.hourly_rate,
                    CASE WHEN sr.shift_date > ? THEN 1 ELSE 0 END as is_upcoming,
                    approver.full_name as approver_name
                FROM shift_registrations sr
                LEFT JOIN teaching_shifts ts ON sr.shift_id = ts.id
                LEFT JOIN users approver ON sr.approved_by = approver.id
                WHERE sr.staff_id = ? 
                AND sr.shift_date BETWEEN ? AND ?
                ORDER BY sr.shift_date ASC, sr.created_at ASC";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([$today, $staffId, $periodStart, $periodEnd]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy thống kê theo trạng thái (chỉ ca đến hôm nay)
     */
    private function getStatusStats($staffId, $periodStart, $periodEnd)
    {
        $today = date('Y-m-d');
        
        $sql = "SELECT 
                    status,
                    COUNT(*) as count,
                    COALESCE(SUM(hours), 0) as total_hours
                FROM shift_registrations
                WHERE staff_id = ? 
                AND shift_date BETWEEN ? AND ?
                AND shift_date <= ?
                GROUP BY status";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([$staffId, $periodStart, $periodEnd, $today]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy thống kê theo ngày (cho biểu đồ) - chỉ ca đến hôm nay
     */
    private function getDailyStats($staffId, $periodStart, $periodEnd)
    {
        $today = date('Y-m-d');
        
        $sql = "SELECT 
                    DATE(shift_date) as date,
                    COUNT(*) as count,
                    COALESCE(SUM(hours), 0) as total_hours
                FROM shift_registrations
                WHERE staff_id = ? 
                AND shift_date BETWEEN ? AND ?
                AND shift_date <= ?
                GROUP BY DATE(shift_date)
                ORDER BY date";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([$staffId, $periodStart, $periodEnd, $today]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Export thống kê ra Excel
     */
    public function exportExcel()
    {
        $user = $this->getUser();
        $staffId = $user['id'];
        
        $month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
        $year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
        
        $periodStart = date('Y-m-01', strtotime("$year-$month-01"));
        $periodEnd = date('Y-m-t', strtotime("$year-$month-01"));
        
        try {
            $shifts = $this->getStaffShifts($staffId, $periodStart, $periodEnd);
            $stats = $this->getStaffShiftStats($staffId, $periodStart, $periodEnd);
            
            // Tạo file Excel đơn giản bằng CSV
            $filename = 'thong-ke-ca-day-' . $user['full_name'] . '-' . $year . '-' . sprintf('%02d', $month) . '.csv';
            
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            
            // BOM cho UTF-8
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header
            fputcsv($output, ['THỐNG KÊ CA DẠY - ' . strtoupper($user['full_name'])]);
            fputcsv($output, ['Tháng ' . $month . '/' . $year]);
            fputcsv($output, []);
            
            // Tổng quan
            fputcsv($output, ['TỔNG QUAN']);
            fputcsv($output, ['Tổng ca dạy', $stats['total_shifts']]);
            fputcsv($output, ['Tổng giờ dạy', $stats['total_hours']]);
            fputcsv($output, ['Tổng thu nhập', number_format($stats['total_amount'], 0, ',', '.')]);
            fputcsv($output, ['Ca đã duyệt', $stats['approved_shifts']]);
            fputcsv($output, ['Ca chờ duyệt', $stats['pending_shifts']]);
            fputcsv($output, []);
            
            // Chi tiết
            fputcsv($output, ['CHI TIẾT CA DẠY']);
            fputcsv($output, ['Ngày', 'Ca', 'Giờ bắt đầu', 'Giờ kết thúc', 'Số giờ', 'Đơn giá', 'Thành tiền', 'Trạng thái', 'Ghi chú']);
            
            foreach ($shifts as $shift) {
                fputcsv($output, [
                    date('d/m/Y', strtotime($shift['shift_date'])),
                    $shift['shift_name'] ?? 'Ca tự chọn',
                    $shift['custom_start'] ?: $shift['start_time'],
                    $shift['custom_end'] ?: $shift['end_time'],
                    $shift['hours'],
                    number_format($shift['hourly_rate'] ?? 0, 0, ',', '.'),
                    number_format($shift['amount'], 0, ',', '.'),
                    $this->getStatusText($shift['status']),
                    $shift['notes']
                ]);
            }
            
            fclose($output);
            exit;
            
        } catch (Exception $e) {
            error_log('Export shift stats error: ' . $e->getMessage());
            $_SESSION['error'] = 'Không thể xuất file: ' . $e->getMessage();
            $this->redirect('/Quan_ly_trung_tam/public/staff/shift-stats');
        }
    }

    private function getStatusText($status)
    {
        $statusMap = [
            'pending' => 'Chờ duyệt',
            'approved' => 'Đã duyệt',
            'rejected' => 'Từ chối',
            'cancelled' => 'Đã hủy'
        ];
        return $statusMap[$status] ?? $status;
    }
}
