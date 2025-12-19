<?php

require_once __DIR__ . '/../models/RevenueReport.php';

class TransferBatchController extends BaseController
{
    private $revenueModel;

    public function __construct()
    {
        parent::__construct();
        $this->revenueModel = new RevenueReport();
        $this->requireAuth();
        $this->requireRole('admin');
    }

    /**
     * Hiển thị báo cáo quản lý đợt chuyển tiền (chỉ xem, không lưu DB)
     */
    public function index()
    {
        try {
            // Lấy filter từ GET
            $dateFrom = $_GET['from_date'] ?? null;
            $dateTo = $_GET['to_date'] ?? null;
            
            // Mặc định: tuần này nếu không có filter
            if (!$dateFrom || !$dateTo) {
                $today = new DateTime();
                $dayOfWeek = $today->format('w'); // 0 = Sunday
                $monday = (clone $today)->modify('-' . ($dayOfWeek == 0 ? 6 : $dayOfWeek - 1) . ' days');
                $sunday = (clone $monday)->modify('+6 days');
                
                $dateFrom = $monday->format('Y-m-d');
                $dateTo = $sunday->format('Y-m-d');
            }
            
            // Tính toán số liệu
            $data = $this->calculateTransferData($dateFrom, $dateTo);
            
            $this->view('transfer_batch/index', [
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'data' => $data
            ]);
        } catch (Exception $e) {
            $this->view('transfer_batch/index', [
                'dateFrom' => date('Y-m-d'),
                'dateTo' => date('Y-m-d'),
                'data' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Tính toán dữ liệu chuyển tiền theo khoảng thời gian
     */
    private function calculateTransferData($dateFrom, $dateTo)
    {
        // Lấy database instance
        $db = Database::getInstance();
        
        $sql = "SELECT 
                    COALESCE(SUM(amount), 0) as total_revenue,
                    COALESCE(SUM(CASE WHEN transfer_type = 'account_company' THEN amount ELSE 0 END), 0) as transfer_company,
                    COALESCE(SUM(CASE WHEN transfer_type = 'account_thay_hien' THEN amount ELSE 0 END), 0) as transfer_thien,
                    COALESCE(SUM(CASE WHEN transfer_type IN ('account_co_nhi', 'cash') THEN amount ELSE 0 END), 0) as cash_thien
                FROM revenue_reports
                WHERE DATE(payment_date) BETWEEN ? AND ?";
        
        $result = $db->fetch($sql, [$dateFrom, $dateTo]);
        
        if ($result) {
            // Chuyển đổi sang float để đảm bảo không có null
            $result['total_revenue'] = (float)$result['total_revenue'];
            $result['transfer_company'] = (float)$result['transfer_company'];
            $result['transfer_thien'] = (float)$result['transfer_thien'];
            $result['cash_thien'] = (float)$result['cash_thien'];
            $result['difference'] = $result['total_revenue'] - ($result['transfer_company'] + $result['transfer_thien'] + $result['cash_thien']);
            $result['date_from'] = $dateFrom;
            $result['date_to'] = $dateTo;
            
            return $result;
        }
        
        return [
            'total_revenue' => 0,
            'transfer_company' => 0,
            'transfer_thien' => 0,
            'cash_thien' => 0,
            'difference' => 0,
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ];
    }
}
