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
            'total_visitors_month' => 150,
            'total_registered_month' => 85,
            'total_revenue_month' => 25000000
        ];
        
        // Get recent reports (tất cả nhân viên)
        $recent_reports = [
            [
                'report_date' => date('Y-m-d'),
                'report_time' => date('H:i:s'),
                'staff_name' => 'Nguyễn Văn A',
                'total_visitors' => 10,
                'total_registered' => 6
            ],
            [
                'report_date' => date('Y-m-d', strtotime('-1 day')),
                'report_time' => '14:30:00',
                'staff_name' => 'Trần Thị B',
                'total_visitors' => 8,
                'total_registered' => 5
            ]
        ];
        
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
        // Tạm thời return sample data, sau này sẽ query database
        return rand(1, 5);
    }

    private function getMyTodayRevenue($staff_id) 
    {
        // Tạm thời return sample data
        return rand(500000, 2000000);
    }

    private function getMyVisitorsToday($staff_id)
    {
        // Tạm thời return sample data
        return rand(5, 20);
    }

    private function getMyCertificatesPending($staff_id)
    {
        // Tạm thời return sample data
        return rand(0, 3);
    }

    private function getMyRecentReports($staff_id)
    {
        // Sample data cho báo cáo của nhân viên hiện tại
        return [
            [
                'report_date' => date('Y-m-d'),
                'report_time' => date('H:i:s'),
                'total_visitors' => rand(8, 15),
                'total_registered' => rand(3, 8),
                'revenue_amount' => rand(500000, 1500000)
            ],
            [
                'report_date' => date('Y-m-d', strtotime('-1 day')),
                'report_time' => '16:30:00',
                'total_visitors' => rand(5, 12),
                'total_registered' => rand(2, 6),
                'revenue_amount' => rand(300000, 1200000)
            ]
        ];
    }
}