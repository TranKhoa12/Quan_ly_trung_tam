<?php

class DashboardController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        // Tạm thời tắt authentication để test
        // $this->requireAuth();
    }

    public function index()
    {
        // Lấy thông tin user từ session hoặc dùng test data
        $user = [
            'id' => $_SESSION['user_id'] ?? 1,
            'username' => $_SESSION['username'] ?? 'test_user',
            'full_name' => $_SESSION['full_name'] ?? 'Nhân Viên Test',
            'role' => $_SESSION['role'] ?? 'staff'
        ];
        
        // Phân quyền dashboard dựa trên role
        if ($user['role'] === 'staff') {
            $this->staffDashboard($user);
        } else {
            $this->adminDashboard($user);
        }
    }

    public function staffDashboard($user)
    {
        // Dashboard cho nhân viên - chỉ xem dữ liệu của mình
        try {
            $stats = [
                'my_reports_today' => $this->getMyTodayReportsCount($user['id']),
                'my_revenue_today' => $this->getMyTodayRevenue($user['id']),
                'my_visitors_today' => $this->getMyVisitorsToday($user['id']),
                'my_certificates_pending' => $this->getMyCertificatesPending($user['id'])
            ];
        } catch (Exception $e) {
            // Nếu có lỗi, dùng dữ liệu mặc định
            $stats = [
                'my_reports_today' => 0,
                'my_revenue_today' => 0,
                'my_visitors_today' => 0,
                'my_certificates_pending' => 0
            ];
        }
        
        // Lấy báo cáo gần đây của nhân viên
        try {
            $my_recent_reports = $this->getMyRecentReports($user['id']);
        } catch (Exception $e) {
            $my_recent_reports = [];
        }
        
        // Sử dụng giao diện chính của staff
        $this->view('dashboard/staff', [
            'user' => $user,
            'stats' => $stats,
            'my_recent_reports' => $my_recent_reports
        ]);
    }

    public function adminDashboard($user)
    {
        // Dashboard cho admin - xem toàn bộ dữ liệu
        $stats = [
            'total_reports_today' => $this->getTodayReportsCount(),
            'total_revenue_today' => $this->getTodayRevenue(),
            'total_students' => $this->getTotalStudents(),
            'pending_certificates' => $this->getPendingCertificates(),
            'total_visitors_month' => $this->getMonthVisitors(),
            'total_registered_month' => $this->getMonthRegistered(),
            'total_revenue_month' => $this->getMonthRevenue()
        ];
        
        // Get recent reports (tất cả nhân viên)
        $recent_reports = $this->getRecentReports();
        
        // Sử dụng layout mới cho giao diện admin hiện đại
        $this->view('dashboard/admin-modern', [
            'user' => $user,
            'stats' => $stats,
            'recent_reports' => $recent_reports
        ]);
    }

    private function getTodayReportsCount()
    {
        if (!$this->db) {
            return 0;
        }
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count FROM reports WHERE DATE(created_at) = CURDATE()"
        );
        return $result['count'] ?? 0;
    }

    private function getTodayRevenue()
    {
        if (!$this->db) {
            return 0;
        }
        $result = $this->db->fetch(
            "SELECT SUM(amount) as total FROM revenue_reports WHERE DATE(payment_date) = CURDATE()"
        );
        return $result['total'] ?? 0;
    }

    private function getTotalStudents()
    {
        if (!$this->db) {
            return 0;
        }
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count FROM students WHERE status = 'studying'"
        );
        return $result['count'] ?? 0;
    }

    private function getPendingCertificates()
    {
        if (!$this->db) {
            return 0;
        }
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count FROM certificates WHERE approval_status = 'pending'"
        );
        return $result['count'] ?? 0;
    }

    // Methods for Staff Dashboard
    private function getMyTodayReportsCount($staff_id)
    {
        if (!$this->db) {
            return 0;
        }
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count FROM reports 
             WHERE staff_id = ? AND DATE(report_date) = CURDATE()",
            [$staff_id]
        );
        return $result['count'] ?? 0;
    }

    private function getMyTodayRevenue($staff_id) 
    {
        if (!$this->db) {
            return 0;
        }
        $result = $this->db->fetch(
            "SELECT SUM(rr.amount) as total 
             FROM revenue_reports rr
             INNER JOIN reports r ON DATE(rr.payment_date) = r.report_date
             WHERE r.staff_id = ? AND DATE(rr.payment_date) = CURDATE()",
            [$staff_id]
        );
        return $result['total'] ?? 0;
    }

    private function getMyVisitorsToday($staff_id)
    {
        if (!$this->db) {
            return 0;
        }
        $result = $this->db->fetch(
            "SELECT SUM(total_visitors) as total FROM reports 
             WHERE staff_id = ? AND DATE(report_date) = CURDATE()",
            [$staff_id]
        );
        return $result['total'] ?? 0;
    }

    private function getMyCertificatesPending($staff_id)
    {
        if (!$this->db) {
            return 0;
        }
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count FROM certificates 
             WHERE created_by = ? AND approval_status = 'pending'",
            [$staff_id]
        );
        return $result['count'] ?? 0;
    }

    private function getMyRecentReports($staff_id)
    {
        if (!$this->db) {
            return [];
        }
        $reports = $this->db->fetchAll(
            "SELECT report_date, report_time, total_visitors, total_registered
             FROM reports
             WHERE staff_id = ?
             ORDER BY report_date DESC, report_time DESC
             LIMIT 5",
            [$staff_id]
        );
        return $reports ?? [];
    }

    private function getMonthVisitors()
    {
        if (!$this->db) {
            return 0;
        }
        $result = $this->db->fetch(
            "SELECT SUM(total_visitors) as total FROM reports 
             WHERE MONTH(report_date) = MONTH(CURRENT_DATE()) 
             AND YEAR(report_date) = YEAR(CURRENT_DATE())"
        );
        return $result['total'] ?? 0;
    }

    private function getMonthRegistered()
    {
        if (!$this->db) {
            return 0;
        }
        $result = $this->db->fetch(
            "SELECT SUM(total_registered) as total FROM reports 
             WHERE MONTH(report_date) = MONTH(CURRENT_DATE()) 
             AND YEAR(report_date) = YEAR(CURRENT_DATE())"
        );
        return $result['total'] ?? 0;
    }

    private function getMonthRevenue()
    {
        if (!$this->db) {
            return 0;
        }
        $result = $this->db->fetch(
            "SELECT SUM(amount) as total FROM revenue_reports 
             WHERE MONTH(payment_date) = MONTH(CURRENT_DATE()) 
             AND YEAR(payment_date) = YEAR(CURRENT_DATE())"
        );
        return $result['total'] ?? 0;
    }

    private function getRecentReports()
    {
        if (!$this->db) {
            return [];
        }
        $reports = $this->db->fetchAll(
            "SELECT r.report_date, r.report_time, r.total_visitors, r.total_registered,
                    u.full_name as staff_name
             FROM reports r
             LEFT JOIN users u ON r.staff_id = u.id
             ORDER BY r.report_date DESC, r.report_time DESC
             LIMIT 10"
        );
        return $reports ?? [];
    }
}