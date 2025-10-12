<?php

class DashboardController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth(); // Bật lại authentication
    }

    public function index()
    {
        // Lấy thông tin user thực từ session
        $user = [
            'id' => $_SESSION['user_id'] ?? 1,
            'username' => $_SESSION['username'] ?? 'guest',
            'full_name' => $_SESSION['full_name'] ?? 'Guest User',
            'role' => $_SESSION['role'] ?? 'staff'
        ];
        
        // Get dashboard statistics
        $stats = [
            'total_reports_today' => 0, // $this->getTodayReportsCount(),
            'total_revenue_today' => 0, // $this->getTodayRevenue(),
            'total_students' => 0, // $this->getTotalStudents(),
            'pending_certificates' => 0, // $this->getPendingCertificates()
            'total_visitors_month' => 150, // Sample data
            'total_registered_month' => 85, // Sample data  
            'total_revenue_month' => 25000000 // Sample data
        ];
        
        // Get recent reports (sample data)
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
        
        $this->view('dashboard/index', [
            'user' => $user,
            'stats' => $stats,
            'recent_reports' => $recent_reports
        ]);
    }

    private function getTodayReportsCount()
    {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count FROM reports WHERE DATE(created_at) = CURDATE()"
        );
        return $result['count'] ?? 0;
    }

    private function getTodayRevenue()
    {
        $result = $this->db->fetch(
            "SELECT SUM(amount) as total FROM revenue_reports WHERE DATE(payment_date) = CURDATE()"
        );
        return $result['total'] ?? 0;
    }

    private function getTotalStudents()
    {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count FROM students WHERE status = 'studying'"
        );
        return $result['count'] ?? 0;
    }

    private function getPendingCertificates()
    {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count FROM certificates WHERE approval_status = 'pending'"
        );
        return $result['count'] ?? 0;
    }
}