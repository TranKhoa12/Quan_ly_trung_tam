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
     * API endpoint để đưa dữ liệu vào Google Sheet
     */
    public function pushToGoogleSheet()
    {
        try {
            // Chỉ chấp nhận POST request
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            // Lấy dữ liệu từ POST
            $dateFrom = $_POST['from_date'] ?? null;
            $dateTo = $_POST['to_date'] ?? null;

            if (!$dateFrom || !$dateTo) {
                throw new Exception('Thiếu thông tin ngày tháng');
            }

            // Tính toán dữ liệu
            $data = $this->calculateTransferData($dateFrom, $dateTo);

            // ID của Google Sheet
            $spreadsheetId = '17ATz02rJKSfV5rBrQGoei-W9nTCr0-hmYZzCQ1zuzD4';

            // Chuẩn bị dữ liệu để ghi
            // Cột A, B: Hình CK (để trống)
            // Cột C-I: Dữ liệu thực tế
            $rowData = [
                '', // Cột A: Hình CK trống
                '', // Cột B: Hình CK trống
                date('d/m/Y', strtotime($dateFrom)), // Cột C: Từ ngày
                date('d/m/Y', strtotime($dateTo)), // Cột D: Đến ngày
                $data['total_revenue'], // Cột E: Tổng thực thu
                $data['transfer_company'], // Cột F: Chuyển khoản TK CTY
                $data['transfer_thien'], // Cột G: Chuyển khoản TK Th Hiến
                $data['cash_thien'], // Cột H: Chi nhánh CK cho Th Hiến
                $data['difference'], // Cột I: Chênh lệch
                '', // Cột J: Hình CK1 trống
                '' // Cột K: Hình CK2 trống
            ];

            // Ghi vào Google Sheet
            $writeInfo = $this->writeToGoogleSheet($spreadsheetId, $rowData);

            echo json_encode([
                'success' => true,
                'message' => 'Đã đưa dữ liệu vào Google Sheet thành công!',
                'debug' => $writeInfo
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Ghi dữ liệu vào Google Sheet
     */
    private function writeToGoogleSheet($spreadsheetId, $rowData)
    {
        // Đường dẫn tới service account credentials
        $credentialsPath = __DIR__ . '/../../config/google-credentials.json';

        if (!file_exists($credentialsPath)) {
            throw new Exception('Không tìm thấy file credentials Google API. Vui lòng tham khảo file GOOGLE_SHEETS_SETUP.md để cấu hình.');
        }

        try {
            // Khởi tạo Google Client
            $client = new \Google_Client();
            $client->setApplicationName('Quan ly trung tam');
            $client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
            $client->setAuthConfig($credentialsPath);
            $client->setAccessType('offline');

            // Khởi tạo Sheets service
            $service = new \Google_Service_Sheets($client);

            // Tên sheet cố định (theo ảnh của bạn)
            $sheetName = 'Danh sách chuyển tiền';

            // Tìm dòng trống tiếp theo và kiểm tra trùng (bỏ qua dòng trống)
            $range = "{$sheetName}!C:I"; // Chỉ đọc cột có dữ liệu
            
            try {
                $response = $service->spreadsheets_values->get($spreadsheetId, $range);
                $values = $response->getValues();
                
                // Lấy ngày từ dữ liệu cần ghi (cột C và D = index 2 và 3 trong rowData)
                $dateFromToWrite = $rowData[2]; // Cột C
                $dateToToWrite = $rowData[3];    // Cột D
                
                if ($values) {
                    // Kiểm tra trùng khoảng thời gian
                    foreach ($values as $rowIndex => $row) {
                        // Bỏ qua dòng header hoặc dòng trống
                        if ($rowIndex == 0 || empty($row[0])) {
                            continue;
                        }
                        
                        // Kiểm tra cột Từ ngày (C) và Đến ngày (D)
                        $existingDateFrom = isset($row[0]) ? trim($row[0]) : '';
                        $existingDateTo = isset($row[1]) ? trim($row[1]) : '';
                        
                        if ($existingDateFrom === $dateFromToWrite && $existingDateTo === $dateToToWrite) {
                            throw new Exception("Dữ liệu khoảng thời gian từ {$dateFromToWrite} đến {$dateToToWrite} đã tồn tại ở dòng " . ($rowIndex + 1) . ". Không thể thêm trùng!");
                        }
                    }
                    
                    // Tìm dòng cuối cùng có dữ liệu (bỏ qua dòng trống)
                    $nextRow = 1;
                    for ($i = count($values) - 1; $i >= 0; $i--) {
                        // Kiểm tra nếu dòng có ít nhất 1 cell không trống
                        $hasData = false;
                        foreach ($values[$i] as $cell) {
                            if (!empty($cell)) {
                                $hasData = true;
                                break;
                            }
                        }
                        if ($hasData) {
                            $nextRow = $i + 2; // +2 vì index bắt đầu từ 0 và cần dòng tiếp theo
                            break;
                        }
                    }
                } else {
                    $nextRow = 1;
                }
            } catch (\Exception $e) {
                // Nếu là lỗi trùng lặp, throw lại
                if (strpos($e->getMessage(), 'đã tồn tại') !== false) {
                    throw $e;
                }
                // Nếu sheet trống hoặc lỗi đọc khác, bắt đầu từ dòng 1
                $nextRow = 1;
            }

            // Ghi dữ liệu
            $writeRange = "{$sheetName}!A{$nextRow}:K{$nextRow}";
            $body = new \Google_Service_Sheets_ValueRange([
                'values' => [$rowData]
            ]);
            $params = [
                'valueInputOption' => 'RAW'
            ];

            $result = $service->spreadsheets_values->update($spreadsheetId, $writeRange, $body, $params);
            
            // Trả về thông tin debug
            return [
                'sheetName' => $sheetName,
                'row' => $nextRow,
                'range' => $writeRange,
                'updatedCells' => $result->getUpdatedCells(),
                'updatedRows' => $result->getUpdatedRows()
            ];
            
        } catch (\Google_Service_Exception $e) {
            $error = json_decode($e->getMessage(), true);
            $errorMsg = $error['error']['message'] ?? $e->getMessage();
            throw new Exception("Lỗi Google Sheets API: {$errorMsg}. Kiểm tra: 1) Đã share sheet với service account email chưa? 2) Service account có quyền Editor?");
        } catch (\Exception $e) {
            throw new Exception("Lỗi kết nối Google Sheets: " . $e->getMessage());
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
