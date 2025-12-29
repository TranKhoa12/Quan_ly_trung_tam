<?php

require_once __DIR__ . '/../models/TeachingShift.php';
require_once __DIR__ . '/../models/ShiftRegistration.php';
require_once __DIR__ . '/../models/ShiftPayroll.php';
require_once __DIR__ . '/../models/TaxWithholdingLedger.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/ShiftTransfer.php';
require_once __DIR__ . '/../models/ShiftTransferLog.php';

class TeachingShiftController extends BaseController
{
    private $shiftModel;
    private $registrationModel;
    private $payrollModel;
    private $taxLedgerModel;
    private $userModel;
    private $transferModel;
    private $transferLogModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->shiftModel = new TeachingShift();
        $this->registrationModel = new ShiftRegistration();
        $this->payrollModel = new ShiftPayroll();
        $this->taxLedgerModel = new TaxWithholdingLedger();
        $this->userModel = new User();
        $this->transferModel = new ShiftTransfer();
        $this->transferLogModel = new ShiftTransferLog();
    }

    public function index()
    {
        $user = $this->getUser();
        $activeShifts = $this->shiftModel->getActiveShifts();
        
        // Load data for 3 months: previous, current, next
        $fromDate = date('Y-m-01', strtotime('first day of -1 month'));
        $toDate = date('Y-m-t', strtotime('last day of +1 month'));
        $registrations = $this->registrationModel->getForStaff($user['id'], $fromDate, $toDate);

        // Check if user wants calendar view
        $view = $_GET['view'] ?? 'calendar';
        
        if ($view === 'calendar') {
            $this->view('teaching_shifts/calendar', [
                'activeShifts' => $activeShifts,
                'registrations' => $registrations,
                'user' => $user
            ]);
        } else {
            $this->view('teaching_shifts/index', [
                'activeShifts' => $activeShifts,
                'registrations' => $registrations,
                'user' => $user
            ]);
        }
    }

    public function store()
    {
        $user = $this->getUser();
        $shiftId = !empty($_POST['shift_id']) && $_POST['shift_id'] !== 'custom'
            ? (int) $_POST['shift_id']
            : null;
        $shiftDate = $_POST['shift_date'] ?? '';
        $customStart = $_POST['custom_start'] ?? null;
        $customEnd = $_POST['custom_end'] ?? null;
        $notes = trim($_POST['notes'] ?? '');

        try {
            if (empty($shiftDate)) {
                throw new Exception('Vui lòng chọn ngày dạy.');
            }

            if (strtotime($shiftDate) < strtotime(date('Y-m-d'))) {
                throw new Exception('Không thể đăng ký ca cho ngày đã qua.');
            }

            $shiftStart = null;
            $shiftEnd = null;
            $hourlyRate = 50;

            if ($shiftId) {
                $shift = $this->shiftModel->find($shiftId);
                if (!$shift || (int)$shift['is_active'] !== 1) {
                    throw new Exception('Ca dạy không tồn tại hoặc đã bị khóa.');
                }
                $shiftStart = $shift['start_time'];
                $shiftEnd = $shift['end_time'];
                $hourlyRate = (float)$shift['hourly_rate'];
            } else {
                if (empty($customStart) || empty($customEnd)) {
                    throw new Exception('Vui lòng nhập giờ bắt đầu và kết thúc cho Ca chọn.');
                }
                $shiftStart = $customStart;
                $shiftEnd = $customEnd;
            }

            if (strtotime($shiftEnd) <= strtotime($shiftStart)) {
                throw new Exception('Giờ kết thúc phải lớn hơn giờ bắt đầu.');
            }

            $hours = $this->calculateHours($shiftStart, $shiftEnd);
            if ($hours <= 0) {
                throw new Exception('Không thể tính được số giờ cho ca này.');
            }

            if ($this->registrationModel->hasOverlap($user['id'], $shiftDate, $shiftStart, $shiftEnd)) {
                throw new Exception('Bạn đã đăng ký ca trùng thời gian trong ngày này.');
            }

            $this->registrationModel->create([
                'staff_id' => $user['id'],
                'shift_id' => $shiftId,
                'shift_date' => $shiftDate,
                'custom_start' => $shiftId ? null : $shiftStart,
                'custom_end' => $shiftId ? null : $shiftEnd,
                'hours' => $hours,
                'status' => 'pending',
                'notes' => $notes
            ]);

            $_SESSION['success'] = 'Đăng ký ca dạy thành công. Vui lòng chờ admin duyệt.';
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts');
    }

    public function cancel($id)
    {
        $user = $this->getUser();
        $registration = $this->registrationModel->find($id);

        if (!$registration || (int)$registration['staff_id'] !== (int)$user['id']) {
            $_SESSION['error'] = 'Không tìm thấy ca dạy hoặc bạn không có quyền hủy.';
            $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts');
            return;
        }

        if ($registration['status'] !== 'pending') {
            $_SESSION['error'] = 'Chỉ có thể hủy ca ở trạng thái chờ duyệt.';
            $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts');
            return;
        }

        $this->registrationModel->update($id, ['status' => 'cancelled']);
        $_SESSION['success'] = 'Đã hủy đăng ký ca dạy.';
        $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts');
    }

    public function admin()
    {
        $this->requireAdmin();
        
        // Check if user wants calendar view
        $view = $_GET['view'] ?? 'calendar';
        
        if ($view === 'calendar') {
            // For calendar view, fetch data for 3 months: previous, current, next
            $fromDate = date('Y-m-01', strtotime('first day of -1 month'));
            $toDate = date('Y-m-t', strtotime('last day of +1 month'));
            
            // getAdminList already joins staff_name, approver_name, and preset times
            $registrations = $this->registrationModel->getAdminList([
                'date_from' => $fromDate,
                'date_to' => $toDate
            ]);
            
            $staffList = $this->userModel->where(['status' => 'active'], 'full_name ASC');
            
            $this->view('teaching_shifts/admin_calendar', [
                'registrations' => $registrations,
                'staffList' => $staffList
            ]);
        } else {
            // List view with pagination
            $filters = [
                'status' => $_GET['status'] ?? '',
                'date_from' => $_GET['date_from'] ?? '',
                'date_to' => $_GET['date_to'] ?? '',
                'staff_id' => !empty($_GET['staff_id']) ? (int)$_GET['staff_id'] : ''
            ];

            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            $perPage = 20;
            $offset = ($page - 1) * $perPage;

            $allRegistrations = $this->registrationModel->getAdminList($filters);
            $totalRecords = count($allRegistrations);
            $totalPages = ceil($totalRecords / $perPage);
            $registrations = array_slice($allRegistrations, $offset, $perPage);
            
            $staffList = $this->userModel->where(['status' => 'active'], 'full_name ASC');

            $this->view('teaching_shifts/admin', [
                'registrations' => $registrations,
                'staffList' => $staffList,
                'filters' => $filters,
                'pagination' => [
                    'currentPage' => $page,
                    'totalPages' => $totalPages,
                    'totalRecords' => $totalRecords,
                    'perPage' => $perPage
                ]
            ]);
        }
    }

    public function updateStatus($id)
    {
        $this->requireAdmin();
        $registration = $this->registrationModel->find($id);

        if (!$registration) {
            $_SESSION['error'] = 'Không tìm thấy đăng ký ca dạy.';
            $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts/admin');
            return;
        }

        // Accept both 'action' and 'status' formats
        $status = $_POST['status'] ?? '';
        if (empty($status)) {
            $action = $_POST['action'] ?? '';
            if ($action === 'approve') {
                $status = 'approved';
            } elseif ($action === 'reject') {
                $status = 'rejected';
            }
        }

        if (!in_array($status, ['approved', 'rejected', 'pending'], true)) {
            $_SESSION['error'] = 'Hành động không hợp lệ.';
            $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts/admin');
            return;
        }

        // Check if trying to change status from approved and shift is in locked payroll
        if ($registration['status'] === 'approved' && $status !== 'approved') {
            if ($this->payrollModel->isShiftInLockedPayroll($registration['staff_id'], $registration['shift_date'])) {
                $_SESSION['error'] = 'Không thể thay đổi trạng thái ca dạy đã có trong bảng lương đã lưu. Vui lòng hủy bảng lương trước.';
                $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts/admin');
                return;
            }
        }

        // Update data based on status
        $updateData = ['status' => $status];
        
        if ($status === 'approved' || $status === 'rejected') {
            // Set approver info when approving or rejecting
            $updateData['approved_by'] = $this->getUser()['id'];
            $updateData['approved_at'] = date('Y-m-d H:i:s');
        } elseif ($status === 'pending') {
            // Clear approver info when reverting to pending
            $updateData['approved_by'] = null;
            $updateData['approved_at'] = null;
        }

        $this->registrationModel->update($id, $updateData);

        $messages = [
            'approved' => 'Đã duyệt ca dạy.',
            'rejected' => 'Đã từ chối ca dạy.',
            'pending' => 'Đã chuyển ca về chờ duyệt.'
        ];
        $_SESSION['success'] = $messages[$status] ?? 'Đã cập nhật trạng thái ca dạy.';

        // Redirect back to the same view (calendar or list)
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if (strpos($referer, 'view=list') !== false) {
            $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts/admin?view=list');
        } else {
            $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts/admin');
        }
    }

    public function payroll()
    {
        $this->requireAdmin();
        $month = $_GET['month'] ?? date('Y-m');
        $periodStart = date('Y-m-01', strtotime($month . '-01'));
        $periodEnd = date('Y-m-t', strtotime($periodStart));

        $taxRate = 0.10; // Tạm khấu trừ 10% cho part-time
        $totalTax = 0;
        $totalNet = 0;

        $report = $this->registrationModel->aggregateHours($periodStart, $periodEnd);
        $storedPayroll = $this->payrollModel->getByPeriod($periodStart, $periodEnd);
        $storedIndexed = [];
        foreach ($storedPayroll as $row) {
            $storedIndexed[$row['staff_id']] = $row;
        }

        // Bổ sung thông tin thuế và thực nhận
        foreach ($report as &$row) {
            $stored = $storedIndexed[$row['staff_id']] ?? null;
            $gross = (float)($row['total_amount'] ?? 0);

            if ($stored) {
                $tax = (float)($stored['tax_amount'] ?? 0);
                $net = (float)($stored['net_amount'] ?? ($gross - $tax));
                $row['tax_rate'] = (float)($stored['tax_rate'] ?? $taxRate);
            } else {
                $tax = round($gross * $taxRate);
                $net = $gross - $tax;
                $row['tax_rate'] = $taxRate;
            }

            $row['tax_amount'] = $tax;
            $row['net_amount'] = $net;
            $totalTax += $tax;
            $totalNet += $net;
        }
        unset($row);

        $this->view('teaching_shifts/payroll', [
            'month' => $month,
            'periodStart' => $periodStart,
            'periodEnd' => $periodEnd,
            'report' => $report,
            'storedPayroll' => $storedIndexed,
            'taxRate' => $taxRate,
            'totalTax' => $totalTax,
            'totalNet' => $totalNet
        ]);
    }

    public function finalizePayroll()
    {
        $this->requireAdmin();
        $month = $_POST['month'] ?? date('Y-m');
        $periodStart = date('Y-m-01', strtotime($month . '-01'));
        $periodEnd = date('Y-m-t', strtotime($periodStart));
        $generatedBy = $this->getUser()['id'];
        $taxRate = 0.10;

        $report = $this->registrationModel->aggregateHours($periodStart, $periodEnd);
        if (empty($report)) {
            $_SESSION['error'] = 'Không có ca đã duyệt trong kỳ này.';
            $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts/payroll?month=' . $month);
            return;
        }

        foreach ($report as $row) {
            $hours = (float)($row['total_hours'] ?? 0);
            $amount = (float)($row['total_amount'] ?? 0);
            $payrollId = $this->payrollModel->upsertPayroll($row['staff_id'], $periodStart, $periodEnd, $hours, $amount, $generatedBy, $taxRate);
            $taxAmount = round($amount * $taxRate);
            $netAmount = $amount - $taxAmount;
            $this->taxLedgerModel->upsertLedger($payrollId, $row['staff_id'], $periodStart, $periodEnd, $amount, $taxRate, $taxAmount, $netAmount);
        }

        $_SESSION['success'] = 'Đã lưu bảng lương ca dạy cho tháng ' . date('m/Y', strtotime($periodStart)) . '.';
        $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts/payroll?month=' . $month);
    }

    public function cancelPayroll()
    {
        $this->requireAdmin();
        $month = $_POST['month'] ?? date('Y-m');
        $periodStart = date('Y-m-01', strtotime($month . '-01'));
        $periodEnd = date('Y-m-t', strtotime($periodStart));

        // Check if active payroll exists
        $storedPayroll = $this->payrollModel->getByPeriod($periodStart, $periodEnd, 'active');
        if (empty($storedPayroll)) {
            $_SESSION['error'] = 'Không tìm thấy bảng lương hoạt động cho tháng này.';
            $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts/payroll?month=' . $month);
            return;
        }

        // Cancel all active payroll records for this period
        $this->payrollModel->cancelByPeriod($periodStart, $periodEnd);
        $this->taxLedgerModel->cancelByPeriod($periodStart, $periodEnd);
        
        $_SESSION['success'] = 'Đã hủy bảng lương tháng ' . date('m/Y', strtotime($periodStart)) . ' thành công.';
        $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts/payroll?month=' . $month);
    }

    public function saveStaffPayroll()
    {
        $this->requireAdmin();
        $month = $_POST['month'] ?? date('Y-m');
        $staffId = $_POST['staff_id'] ?? null;
        $totalHours = (float)($_POST['total_hours'] ?? 0);
        $totalAmount = (float)($_POST['total_amount'] ?? 0);
        $taxRate = 0.10;
        
        $periodStart = date('Y-m-01', strtotime($month . '-01'));
        $periodEnd = date('Y-m-t', strtotime($periodStart));
        $generatedBy = $this->getUser()['id'];

        if (!$staffId) {
            $_SESSION['error'] = 'Thiếu thông tin nhân viên.';
            $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts/payroll?month=' . $month);
            return;
        }

        $payrollId = $this->payrollModel->upsertPayroll($staffId, $periodStart, $periodEnd, $totalHours, $totalAmount, $generatedBy, $taxRate);
        $taxAmount = round($totalAmount * $taxRate);
        $netAmount = $totalAmount - $taxAmount;
        $this->taxLedgerModel->upsertLedger($payrollId, $staffId, $periodStart, $periodEnd, $totalAmount, $taxRate, $taxAmount, $netAmount);
        
        $_SESSION['success'] = 'Đã lưu bảng lương cho nhân viên thành công.';
        $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts/payroll?month=' . $month);
    }

    public function cancelStaffPayroll()
    {
        $this->requireAdmin();
        $month = $_POST['month'] ?? date('Y-m');
        $staffId = $_POST['staff_id'] ?? null;
        
        $periodStart = date('Y-m-01', strtotime($month . '-01'));
        $periodEnd = date('Y-m-t', strtotime($periodStart));

        if (!$staffId) {
            $_SESSION['error'] = 'Thiếu thông tin nhân viên.';
            $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts/payroll?month=' . $month);
            return;
        }

        // Cancel payroll for specific staff
        $existing = $this->payrollModel->findByPeriod($staffId, $periodStart, $periodEnd);
        if ($existing) {
            $this->payrollModel->update($existing['id'], ['status' => 'cancelled']);
            $this->taxLedgerModel->cancelStaff($staffId, $periodStart, $periodEnd);
            $_SESSION['success'] = 'Đã hủy bảng lương cho nhân viên.';
        } else {
            $_SESSION['error'] = 'Không tìm thấy bảng lương cho nhân viên này.';
        }
        
        $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts/payroll?month=' . $month);
    }

    public function printPayslip()
    {
        $this->requireAdmin();
        $month = $_GET['month'] ?? date('Y-m');
        $staffId = $_GET['staff_id'] ?? null;
        
        $periodStart = date('Y-m-01', strtotime($month . '-01'));
        $periodEnd = date('Y-m-t', strtotime($periodStart));

        if ($staffId) {
            // Print for single staff
            $payroll = $this->payrollModel->findByPeriod($staffId, $periodStart, $periodEnd);
            if (!$payroll || $payroll['status'] !== 'active') {
                $_SESSION['error'] = 'Không tìm thấy bảng lương đã lưu cho nhân viên này.';
                $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts/payroll?month=' . $month);
                return;
            }
            
            // Get staff info
            $staff = $this->userModel->find($staffId);
            
            // Get shift details
            $shifts = $this->registrationModel->getForStaff($staffId, $periodStart, $periodEnd);
            $approvedShifts = array_filter($shifts, function($shift) {
                return $shift['status'] === 'approved';
            });
            
            $this->view('teaching_shifts/print_payslip', [
                'payroll' => $payroll,
                'staff' => $staff,
                'shifts' => $approvedShifts,
                'periodStart' => $periodStart,
                'periodEnd' => $periodEnd,
                'month' => $month
            ]);
        } else {
            // Print for all staff
            $payrolls = $this->payrollModel->getByPeriod($periodStart, $periodEnd, 'active');
            if (empty($payrolls)) {
                $_SESSION['error'] = 'Không có bảng lương đã lưu trong tháng này.';
                $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts/payroll?month=' . $month);
                return;
            }
            
            $this->view('teaching_shifts/print_payslip_all', [
                'payrolls' => $payrolls,
                'periodStart' => $periodStart,
                'periodEnd' => $periodEnd,
                'month' => $month
            ]);
        }
    }

    public function payrollReport()
    {
        $this->requireAdmin();
        
        // Enable error display for debugging
        ini_set('display_errors', 1);
        error_reporting(E_ALL);
        
        // Get last 12 months of payroll data
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = date('Y-m-01', strtotime("-$i months"));
            $months[] = [
                'key' => date('Y-m', strtotime($date)),
                'display' => date('m/Y', strtotime($date)),
                'start' => date('Y-m-01', strtotime($date)),
                'end' => date('Y-m-t', strtotime($date))
            ];
        }
        
        // Get statistics for each month
        $statistics = [];
        foreach ($months as $month) {
            $payrolls = $this->payrollModel->getByPeriod($month['start'], $month['end'], 'active');
            
            $totalStaff = count($payrolls);
            $totalAmount = array_sum(array_column($payrolls, 'total_amount'));
            $totalHours = array_sum(array_column($payrolls, 'total_hours'));
            
            $statistics[] = [
                'month' => $month['display'],
                'month_key' => $month['key'],
                'total_staff' => $totalStaff,
                'total_amount' => (float)$totalAmount,
                'total_hours' => (float)$totalHours,
                'avg_amount' => $totalStaff > 0 ? $totalAmount / $totalStaff : 0
            ];
        }
        
        // Get staff comparison (top earners)
        $currentMonth = date('Y-m');
        $currentStart = date('Y-m-01');
        $currentEnd = date('Y-m-t');
        $currentPayrolls = $this->payrollModel->getByPeriod($currentStart, $currentEnd, 'active');
        
        // Sort by amount
        usort($currentPayrolls, function($a, $b) {
            return ((float)$b['total_amount']) <=> ((float)$a['total_amount']);
        });
        
        $topStaff = array_slice($currentPayrolls, 0, 10);
        
        $this->view('teaching_shifts/payroll_report', [
            'statistics' => $statistics,
            'topStaff' => $topStaff,
            'currentMonth' => date('m/Y')
        ]);
    }

    public function taxReport()
    {
        $this->requireAdmin();
        $year = $_GET['year'] ?? date('Y');
        $month = $_GET['month'] ?? '';

        $year = preg_replace('/[^0-9]/', '', $year);
        if (strlen($year) !== 4) {
            $year = date('Y');
        }

        $monthlySummary = [];
        $yearTotals = $this->taxLedgerModel->getYearTotals($year) ?? [
            'gross_sum' => 0,
            'tax_sum' => 0,
            'net_sum' => 0,
            'staff_count' => 0,
            'periods' => 0
        ];

        $monthDetails = [];
        $monthTotals = null;

        if (!empty($month)) {
            // Expect format YYYY-MM
            if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
                $_SESSION['error'] = 'Tháng không hợp lệ';
                $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts/tax-report?year=' . $year);
                return;
            }
            $periodStart = date('Y-m-01', strtotime($month . '-01'));
            $periodEnd = date('Y-m-t', strtotime($periodStart));
            $monthDetails = $this->taxLedgerModel->getMonthStaffDetail($periodStart, $periodEnd) ?? [];
            $monthTotals = [
                'gross_sum' => array_sum(array_column($monthDetails, 'gross_sum')),
                'tax_sum' => array_sum(array_column($monthDetails, 'tax_sum')),
                'net_sum' => array_sum(array_column($monthDetails, 'net_sum')),
                'staff_count' => count($monthDetails)
            ];
        } else {
            $monthlySummary = $this->taxLedgerModel->getMonthlySummary($year) ?? [];
        }

        $this->view('teaching_shifts/tax_report', [
            'year' => $year,
            'month' => $month,
            'monthlySummary' => $monthlySummary,
            'yearTotals' => $yearTotals,
            'monthDetails' => $monthDetails,
            'monthTotals' => $monthTotals
        ]);
    }

    public function exportTaxReport()
    {
        $this->requireAdmin();
        $year = $_GET['year'] ?? date('Y');
        $month = $_GET['month'] ?? '';

        $year = preg_replace('/[^0-9]/', '', $year);
        if (strlen($year) !== 4) {
            $year = date('Y');
        }

        if (!empty($month)) {
            if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
                $_SESSION['error'] = 'Tháng không hợp lệ';
                $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts/tax-report?year=' . $year);
                return;
            }
            $periodStart = date('Y-m-01', strtotime($month . '-01'));
            $periodEnd = date('Y-m-t', strtotime($periodStart));
            $rows = $this->taxLedgerModel->getMonthStaffDetail($periodStart, $periodEnd) ?? [];

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="tax-report-' . $month . '.csv"');
            echo "\xEF\xBB\xBF"; // UTF-8 BOM for Excel
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Tháng', 'Nhân viên', 'Gross', 'Thuế', 'Net']);
            foreach ($rows as $row) {
                fputcsv($out, [
                    $month,
                    $row['full_name'],
                    $row['gross_sum'],
                    $row['tax_sum'],
                    $row['net_sum']
                ]);
            }
            fclose($out);
            exit;
        }

        // Export year summary
        $rows = $this->taxLedgerModel->getMonthlySummary($year) ?? [];
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="tax-report-' . $year . '.csv"');
        echo "\xEF\xBB\xBF";
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Tháng', 'Gross', 'Thuế', 'Net', 'Số NV']);
        foreach ($rows as $row) {
            fputcsv($out, [
                $row['month_key'],
                $row['gross_sum'],
                $row['tax_sum'],
                $row['net_sum'],
                $row['staff_count']
            ]);
        }
        fclose($out);
        exit;
    }

    public function delete($id)
    {
        $this->requireAdmin();
        $registration = $this->registrationModel->find($id);

        if (!$registration) {
            $_SESSION['error'] = 'Không tìm thấy ca dạy.';
            $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts/admin');
            return;
        }

        // Check if shift is in locked payroll
        if ($this->payrollModel->isShiftInLockedPayroll($registration['staff_id'], $registration['shift_date'])) {
            $_SESSION['error'] = 'Không thể xóa ca dạy đã có trong bảng lương đã lưu. Vui lòng hủy bảng lương trước.';
            $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts/admin');
            return;
        }

        // Delete the shift registration
        $this->registrationModel->delete($id);
        
        $_SESSION['success'] = 'Đã xóa ca dạy thành công.';
        
        // Redirect back to the same view
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if (strpos($referer, 'view=list') !== false) {
            $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts/admin?view=list');
        } else {
            $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts/admin');
        }
    }

    public function bulkAction()
    {
        $this->requireAdmin();
        
        $action = $_POST['bulk_action'] ?? '';
        $ids = $_POST['selected_ids'] ?? [];
        
        if (empty($ids) || !is_array($ids)) {
            $_SESSION['error'] = 'Vui lòng chọn ít nhất một ca dạy.';
            $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts/admin?view=list');
            return;
        }
        
        $successCount = 0;
        $errorCount = 0;
        $user = $this->getUser();
        
        foreach ($ids as $id) {
            $registration = $this->registrationModel->find($id);
            if (!$registration) {
                $errorCount++;
                continue;
            }
            
            switch ($action) {
                case 'approve':
                    if ($registration['status'] === 'pending') {
                        $this->registrationModel->update($id, [
                            'status' => 'approved',
                            'approved_by' => $user['id'],
                            'approved_at' => date('Y-m-d H:i:s')
                        ]);
                        $successCount++;
                    }
                    break;
                    
                case 'reject':
                    if ($registration['status'] === 'pending') {
                        $this->registrationModel->update($id, [
                            'status' => 'rejected',
                            'approved_by' => $user['id'],
                            'approved_at' => date('Y-m-d H:i:s')
                        ]);
                        $successCount++;
                    }
                    break;
                    
                case 'delete':
                    $this->registrationModel->delete($id);
                    $successCount++;
                    break;
                    
                default:
                    $errorCount++;
            }
        }
        
        if ($successCount > 0) {
            $actionText = [
                'approve' => 'duyệt',
                'reject' => 'từ chối',
                'delete' => 'xóa'
            ][$action] ?? 'xử lý';
            
            $_SESSION['success'] = "Đã {$actionText} thành công {$successCount} ca dạy" . ($errorCount > 0 ? " ({$errorCount} ca thất bại)" : '');
        } else {
            $_SESSION['error'] = 'Không thể thực hiện thao tác.';
        }
        
        $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts/admin?view=list');
    }

    public function quickApprove()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $shiftIds = $input['shift_ids'] ?? [];
        
        if (empty($shiftIds) || !is_array($shiftIds)) {
            echo json_encode(['success' => false, 'message' => 'No shift IDs provided']);
            exit;
        }
        
        $user = $this->getUser();
        $approvedCount = 0;
        
        foreach ($shiftIds as $id) {
            $registration = $this->registrationModel->find($id);
            if ($registration && $registration['status'] === 'pending') {
                $this->registrationModel->update($id, [
                    'status' => 'approved',
                    'approved_by' => $user['id'],
                    'approved_at' => date('Y-m-d H:i:s')
                ]);
                $approvedCount++;
            }
        }
        
        echo json_encode([
            'success' => true,
            'approved_count' => $approvedCount,
            'message' => "Approved {$approvedCount} shift(s)"
        ]);
        exit;
    }

    public function quickReject()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $shiftIds = $input['shift_ids'] ?? [];
        
        if (empty($shiftIds) || !is_array($shiftIds)) {
            echo json_encode(['success' => false, 'message' => 'No shift IDs provided']);
            exit;
        }
        
        $user = $this->getUser();
        $rejectedCount = 0;
        
        foreach ($shiftIds as $id) {
            $registration = $this->registrationModel->find($id);
            if ($registration && $registration['status'] === 'pending') {
                $this->registrationModel->update($id, [
                    'status' => 'rejected',
                    'approved_by' => $user['id'],
                    'approved_at' => date('Y-m-d H:i:s')
                ]);
                $rejectedCount++;
            }
        }
        
        echo json_encode([
            'success' => true,
            'rejected_count' => $rejectedCount,
            'message' => "Rejected {$rejectedCount} shift(s)"
        ]);
        exit;
    }

    // Admin creates shift registration for staff
    public function adminCreate()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->adminStore();
        }
        
        // GET request - show form
        $activeShifts = $this->shiftModel->getActiveShifts();
        $staffList = $this->userModel->where(['status' => 'active'], 'full_name ASC');
        
        $this->view('teaching_shifts/admin_create', [
            'activeShifts' => $activeShifts,
            'staffList' => $staffList
        ]);
    }

    public function adminStore()
    {
        $this->requireAdmin();
        
        $staffId = !empty($_POST['staff_id']) ? (int)$_POST['staff_id'] : 0;
        $shiftId = !empty($_POST['shift_id']) && $_POST['shift_id'] !== 'custom'
            ? (int) $_POST['shift_id']
            : null;
        $shiftDate = $_POST['shift_date'] ?? '';
        $customStart = $_POST['custom_start'] ?? null;
        $customEnd = $_POST['custom_end'] ?? null;
        $notes = trim($_POST['notes'] ?? '');
        $autoApprove = isset($_POST['auto_approve']) && $_POST['auto_approve'] === '1';

        try {
            // Validate staff
            if (empty($staffId)) {
                throw new Exception('Vui lòng chọn nhân viên.');
            }
            
            $staff = $this->userModel->find($staffId);
            if (!$staff) {
                throw new Exception('Nhân viên không tồn tại.');
            }

            if (empty($shiftDate)) {
                throw new Exception('Vui lòng chọn ngày dạy.');
            }

            // Admin có thể đăng ký cho bất kỳ ngày nào (kể cả ngày đã qua)

            $shiftStart = null;
            $shiftEnd = null;
            $hourlyRate = 50;

            if ($shiftId) {
                $shift = $this->shiftModel->find($shiftId);
                if (!$shift || (int)$shift['is_active'] !== 1) {
                    throw new Exception('Ca dạy không tồn tại hoặc đã bị khóa.');
                }
                $shiftStart = $shift['start_time'];
                $shiftEnd = $shift['end_time'];
                $hourlyRate = (float)$shift['hourly_rate'];
            } else {
                if (empty($customStart) || empty($customEnd)) {
                    throw new Exception('Vui lòng nhập giờ bắt đầu và kết thúc cho Ca tùy chỉnh.');
                }
                $shiftStart = $customStart;
                $shiftEnd = $customEnd;
            }

            if (strtotime($shiftEnd) <= strtotime($shiftStart)) {
                throw new Exception('Giờ kết thúc phải lớn hơn giờ bắt đầu.');
            }

            $hours = $this->calculateHours($shiftStart, $shiftEnd);
            if ($hours <= 0) {
                throw new Exception('Không thể tính được số giờ cho ca này.');
            }

            if ($this->registrationModel->hasOverlap($staffId, $shiftDate, $shiftStart, $shiftEnd)) {
                throw new Exception('Nhân viên đã có ca dạy trùng giờ trong ngày này.');
            }

            $data = [
                'staff_id' => $staffId,
                'shift_id' => $shiftId,
                'shift_date' => $shiftDate,
                'custom_start' => $shiftId ? null : $shiftStart,
                'custom_end' => $shiftId ? null : $shiftEnd,
                'hours' => $hours,
                'hourly_rate' => $hourlyRate,
                'notes' => $notes,
                'status' => $autoApprove ? 'approved' : 'pending'
            ];

            // If auto-approve, set approver info
            if ($autoApprove) {
                $admin = $this->getUser();
                $data['approved_by'] = $admin['id'];
                $data['approved_at'] = date('Y-m-d H:i:s');
            }

            $this->registrationModel->create($data);
            
            $message = $autoApprove 
                ? 'Đã đăng ký và duyệt ca dạy cho nhân viên thành công!'
                : 'Đã đăng ký ca dạy cho nhân viên thành công! Ca đang chờ duyệt.';
            
            $_SESSION['success'] = $message;
            $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts/admin');

        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts/admin/create');
        }
    }

    public function adminCreateMultiple()
    {
        $this->requireAdmin();
        
        try {
            $staffIds = $_POST['staff_ids'] ?? [];
            $shiftIds = $_POST['shift_ids'] ?? [];
            $customShifts = $_POST['custom_shifts'] ?? [];
            $dateMode = $_POST['date_mode'] ?? 'single';
            $notes = trim($_POST['notes'] ?? '');
            $autoApprove = isset($_POST['auto_approve']) && $_POST['auto_approve'] === '1';
            
            // Validation
            if (empty($staffIds) || !is_array($staffIds)) {
                throw new Exception('Vui lòng chọn ít nhất một nhân viên.');
            }
            
            if (empty($shiftIds) && empty($customShifts)) {
                throw new Exception('Vui lòng chọn ít nhất một ca dạy hoặc tạo ca tùy chỉnh.');
            }
            
            // Prepare shifts array (combine preset and custom)
            $allShifts = [];
            
            // Add preset shifts
            if (!empty($shiftIds) && is_array($shiftIds)) {
                foreach ($shiftIds as $shiftId) {
                    $shift = $this->shiftModel->find($shiftId);
                    if ($shift && (int)$shift['is_active'] === 1) {
                        $allShifts[] = [
                            'type' => 'preset',
                            'shift_id' => $shiftId,
                            'start' => $shift['start_time'],
                            'end' => $shift['end_time'],
                            'rate' => (float)$shift['hourly_rate']
                        ];
                    }
                }
            }
            
            // Add custom shifts
            if (!empty($customShifts) && is_array($customShifts)) {
                foreach ($customShifts as $customShift) {
                    $parts = explode('|', $customShift);
                    if (count($parts) === 2) {
                        $start = $parts[0];
                        $end = $parts[1];
                        
                        // Validate time format
                        if (strtotime($end) <= strtotime($start)) {
                            throw new Exception('Ca tùy chỉnh: Giờ kết thúc phải lớn hơn giờ bắt đầu.');
                        }
                        
                        $allShifts[] = [
                            'type' => 'custom',
                            'shift_id' => null,
                            'start' => $start,
                            'end' => $end,
                            'rate' => 50.00 // Default hourly rate for custom shifts
                        ];
                    }
                }
            }
            
            if (empty($allShifts)) {
                throw new Exception('Không có ca dạy hợp lệ nào để tạo.');
            }
            
            // Get dates array
            $dates = [];
            if ($dateMode === 'single') {
                $singleDate = $_POST['multi_shift_date'] ?? '';
                if (empty($singleDate)) {
                    throw new Exception('Vui lòng chọn ngày dạy.');
                }
                // Admin có thể đăng ký cho bất kỳ ngày nào (kể cả ngày đã qua)
                $dates[] = $singleDate;
            } else {
                $dateFrom = $_POST['date_from'] ?? '';
                $dateTo = $_POST['date_to'] ?? '';
                $weekdays = $_POST['weekdays'] ?? [];
                
                if (empty($dateFrom) || empty($dateTo)) {
                    throw new Exception('Vui lòng chọn khoảng thời gian.');
                }
                
                // Nếu không chọn ngày nào, mặc định lấy T2-T7 (loại Chủ nhật)
                if (empty($weekdays) || !is_array($weekdays)) {
                    $weekdays = [1, 2, 3, 4, 5, 6]; // T2-T7
                }
                
                // Luôn loại trừ Chủ nhật (0) nếu có
                $weekdays = array_filter($weekdays, function($day) {
                    return $day != 0;
                });
                
                if (empty($weekdays)) {
                    throw new Exception('Vui lòng chọn ít nhất một ngày trong tuần (không bao gồm Chủ nhật).');
                }
                
                if (strtotime($dateFrom) > strtotime($dateTo)) {
                    throw new Exception('Ngày bắt đầu phải trước ngày kết thúc.');
                }
                
                // Generate dates in range matching selected weekdays
                $current = strtotime($dateFrom);
                $end = strtotime($dateTo);
                
                while ($current <= $end) {
                    $dayOfWeek = date('w', $current);
                    // Luôn loại trừ Chủ nhật
                    if ($dayOfWeek != 0 && in_array($dayOfWeek, $weekdays)) {
                        $date = date('Y-m-d', $current);
                        // Admin có thể đăng ký cho bất kỳ ngày nào (kể cả ngày đã qua)
                        $dates[] = $date;
                    }
                    $current = strtotime('+1 day', $current);
                }
                
                if (empty($dates)) {
                    throw new Exception('Không có ngày nào phù hợp trong khoảng thời gian đã chọn.');
                }
            }
            
            // Get admin info for auto-approve
            $admin = $this->getUser();
            $createdCount = 0;
            $skippedCount = 0;
            $errors = [];
            
            // Loop through all combinations
            foreach ($staffIds as $staffId) {
                // Verify staff exists
                $staff = $this->userModel->find($staffId);
                if (!$staff) {
                    $errors[] = "Nhân viên ID $staffId không tồn tại";
                    continue;
                }
                
                foreach ($allShifts as $shiftInfo) {
                    $shiftStart = $shiftInfo['start'];
                    $shiftEnd = $shiftInfo['end'];
                    $hourlyRate = $shiftInfo['rate'];
                    $hours = $this->calculateHours($shiftStart, $shiftEnd);
                    
                    foreach ($dates as $date) {
                        // Check for overlap
                        if ($this->registrationModel->hasOverlap($staffId, $date, $shiftStart, $shiftEnd)) {
                            $skippedCount++;
                            continue;
                        }
                        
                        // Create registration
                        $data = [
                            'staff_id' => $staffId,
                            'shift_id' => $shiftInfo['shift_id'], // null for custom shifts
                            'shift_date' => $date,
                            'custom_start' => $shiftInfo['type'] === 'custom' ? $shiftStart : null,
                            'custom_end' => $shiftInfo['type'] === 'custom' ? $shiftEnd : null,
                            'hours' => $hours,
                            'hourly_rate' => $hourlyRate,
                            'notes' => $notes,
                            'status' => $autoApprove ? 'approved' : 'pending'
                        ];
                        
                        if ($autoApprove) {
                            $data['approved_by'] = $admin['id'];
                            $data['approved_at'] = date('Y-m-d H:i:s');
                        }
                        
                        if ($this->registrationModel->create($data)) {
                            $createdCount++;
                        }
                    }
                }
            }
            
            // Build success message
            $message = "Đã tạo thành công $createdCount ca dạy";
            if ($skippedCount > 0) {
                $message .= " (bỏ qua $skippedCount ca trùng giờ)";
            }
            if (!empty($errors)) {
                $message .= ". Có " . count($errors) . " lỗi xảy ra.";
            }
            
            $_SESSION['success'] = $message;
            if (!empty($errors)) {
                $_SESSION['warning'] = implode('; ', array_slice($errors, 0, 5));
            }
            
            $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts/admin');
            
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts/admin/create');
        }
    }

    private function calculateHours($start, $end)
    {
        $startTs = strtotime($start);
        $endTs = strtotime($end);
        if (!$startTs || !$endTs) {
            return 0;
        }

        $diff = ($endTs - $startTs) / 3600;
        return round($diff, 2);
    }

    // ==================== CHỨC NĂNG CHUYỂN CA ====================

    /**
     * Hiển thị form yêu cầu chuyển ca
     */
    public function transferForm($registrationId)
    {
        $user = $this->getUser();
        
        // Lấy thông tin ca dạy với thời gian từ teaching_shifts nếu cần
        $sql = "SELECT sr.*, 
                       COALESCE(sr.custom_start, ts.start_time) as custom_start,
                       COALESCE(sr.custom_end, ts.end_time) as custom_end,
                       ts.name as shift_name
                FROM shift_registrations sr
                LEFT JOIN teaching_shifts ts ON sr.shift_id = ts.id
                WHERE sr.id = :id";
        
        $registration = $this->db->query($sql, ['id' => $registrationId])->fetch();
        
        if (!$registration) {
            $_SESSION['error'] = 'Không tìm thấy ca dạy.';
            $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts');
            return;
        }

        // Kiểm tra quyền (chỉ người đăng ký mới được chuyển)
        if ((int)$registration['staff_id'] !== (int)$user['id']) {
            $_SESSION['error'] = 'Bạn không có quyền chuyển ca này.';
            $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts');
            return;
        }

        // Kiểm tra ca đã qua chưa
        $shiftDateTime = $registration['shift_date'] . ' ' . ($registration['custom_end'] ?? '23:59:59');
        if (strtotime($shiftDateTime) < time()) {
            $_SESSION['error'] = 'Không thể chuyển ca đã qua hoặc đang diễn ra.';
            $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts');
            return;
        }

        // Kiểm tra ca đã có yêu cầu chuyển chưa
        if ($this->transferModel->hasPendingTransfer($registrationId)) {
            $_SESSION['error'] = 'Ca này đã có yêu cầu chuyển đang chờ duyệt.';
            $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts');
            return;
        }

        // Lấy danh sách nhân viên khác
        $allStaff = $this->userModel->getStaffList();
        $otherStaff = array_filter($allStaff, function($staff) use ($user) {
            return (int)$staff['id'] !== (int)$user['id'];
        });

        // Kiểm tra từng nhân viên có bị trùng ca không
        $staffConflicts = [];
        $shiftStart = $registration['custom_start'];
        $shiftEnd = $registration['custom_end'];
        $shiftDate = $registration['shift_date'];
        
        foreach ($otherStaff as $staff) {
            if ($this->registrationModel->hasOverlap($staff['id'], $shiftDate, $shiftStart, $shiftEnd)) {
                $staffConflicts[$staff['id']] = true;
            }
        }

        $this->view('teaching_shifts/transfer_form', [
            'registration' => $registration,
            'otherStaff' => $otherStaff,
            'staffConflicts' => $staffConflicts,
            'user' => $user
        ]);
    }

    /**
     * Xử lý tạo yêu cầu chuyển ca
     */
    public function transferStore()
    {
        $user = $this->getUser();
        $registrationId = (int)($_POST['registration_id'] ?? 0);
        $toStaffId = (int)($_POST['to_staff_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');

        try {
            // Validate
            $registration = $this->registrationModel->find($registrationId);
            if (!$registration) {
                throw new Exception('Không tìm thấy ca dạy.');
            }

            if ((int)$registration['staff_id'] !== (int)$user['id']) {
                throw new Exception('Bạn không có quyền chuyển ca này.');
            }

            // Kiểm tra ca đã qua chưa
            $shiftDateTime = $registration['shift_date'] . ' ' . ($registration['custom_end'] ?? $registration['end_time'] ?? '23:59:59');
            if (strtotime($shiftDateTime) < time()) {
                throw new Exception('Không thể chuyển ca đã qua hoặc đang diễn ra.');
            }

            if ($this->transferModel->hasPendingTransfer($registrationId)) {
                throw new Exception('Ca này đã có yêu cầu chuyển đang chờ duyệt.');
            }

            if (!$toStaffId) {
                throw new Exception('Vui lòng chọn nhân viên nhận ca.');
            }

            if ($toStaffId === (int)$user['id']) {
                throw new Exception('Không thể chuyển ca cho chính mình.');
            }

            $toStaff = $this->userModel->find($toStaffId);
            if (!$toStaff) {
                throw new Exception('Nhân viên nhận ca không tồn tại.');
            }

            if (empty($reason)) {
                throw new Exception('Vui lòng nhập lý do chuyển ca.');
            }

            // Kiểm tra nhân viên nhận có bị trùng ca không
            $shiftStart = $registration['custom_start'] ?? $registration['start_time'];
            $shiftEnd = $registration['custom_end'] ?? $registration['end_time'];
            if ($this->registrationModel->hasOverlap($toStaffId, $registration['shift_date'], $shiftStart, $shiftEnd)) {
                throw new Exception('Nhân viên nhận đã có ca dạy trùng giờ trong ngày này.');
            }

            // Tạo yêu cầu chuyển ca
            $transferId = $this->transferModel->create([
                'shift_registration_id' => $registrationId,
                'from_staff_id' => $user['id'],
                'to_staff_id' => $toStaffId,
                'reason' => $reason,
                'status' => 'pending'
            ]);

            // Ghi log
            $this->transferLogModel->createLog(
                $transferId,
                'created',
                $user['id'],
                "Tạo yêu cầu chuyển ca cho {$toStaff['full_name']}"
            );

            $_SESSION['success'] = 'Gửi yêu cầu chuyển ca thành công! Vui lòng chờ admin duyệt.';
            $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts/transfers/my');

        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts/transfer/' . $registrationId);
        }
    }

    /**
     * Danh sách yêu cầu chuyển ca của nhân viên
     */
    public function myTransfers()
    {
        $user = $this->getUser();
        $transfers = $this->transferModel->getByStaff($user['id']);

        $this->view('teaching_shifts/my_transfers', [
            'transfers' => $transfers,
            'user' => $user
        ]);
    }

    /**
     * Danh sách yêu cầu chuyển ca (Admin)
     */
    public function transferList()
    {
        $this->requireAdmin();
        
        $status = $_GET['status'] ?? 'pending';
        
        if ($status === 'all') {
            $transfers = $this->transferModel->getAll();
        } else {
            $transfers = $this->transferModel->getByStatus($status);
        }

        $this->view('teaching_shifts/transfer_list', [
            'transfers' => $transfers,
            'currentStatus' => $status,
            'user' => $this->getUser()
        ]);
    }

    /**
     * Duyệt yêu cầu chuyển ca (Admin)
     */
    public function transferApprove($transferId)
    {
        $this->requireAdmin();
        $admin = $this->getUser();
        $adminNote = trim($_POST['admin_note'] ?? '');

        try {
            // Lấy thông tin yêu cầu
            $transfer = $this->transferModel->getDetailById($transferId);
            if (!$transfer) {
                throw new Exception('Không tìm thấy yêu cầu chuyển ca.');
            }

            if ($transfer['status'] !== 'pending') {
                throw new Exception('Yêu cầu này đã được xử lý.');
            }

            // Kiểm tra nhân viên nhận có bị trùng ca không
            $shiftStart = $transfer['custom_start'];
            $shiftEnd = $transfer['custom_end'];
            if ($this->registrationModel->hasOverlap($transfer['to_staff_id'], $transfer['shift_date'], $shiftStart, $shiftEnd)) {
                throw new Exception('Nhân viên nhận hiện đã có ca dạy trùng giờ. Không thể duyệt yêu cầu này.');
            }

            // Bắt đầu transaction
            $this->db->beginTransaction();

            // Duyệt yêu cầu
            $this->transferModel->approve($transferId, $admin['id'], $adminNote);

            // Cập nhật ca dạy: chuyển từ from_staff sang to_staff
            $sql = "UPDATE shift_registrations 
                    SET staff_id = :to_staff_id,
                        notes = CONCAT(COALESCE(notes, ''), :transfer_note)
                    WHERE id = :registration_id";
            
            $this->db->execute($sql, [
                'registration_id' => $transfer['shift_registration_id'],
                'to_staff_id' => $transfer['to_staff_id'],
                'transfer_note' => "\n[Chuyển ca] Từ nhân viên ID {$transfer['from_staff_id']} sang ID {$transfer['to_staff_id']}"
            ]);

            // Ghi log
            $this->transferLogModel->createLog(
                $transferId,
                'approved',
                $admin['id'],
                "Admin duyệt chuyển ca từ {$transfer['from_staff_name']} sang {$transfer['to_staff_name']}. " . ($adminNote ?: '')
            );

            $this->db->commit();

            $_SESSION['success'] = 'Đã duyệt yêu cầu chuyển ca thành công!';

        } catch (Exception $e) {
            $this->db->rollBack();
            $_SESSION['error'] = $e->getMessage();
        }

        $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts/transfers/list');
    }

    /**
     * Từ chối yêu cầu chuyển ca (Admin)
     */
    public function transferReject($transferId)
    {
        $this->requireAdmin();
        $admin = $this->getUser();
        $adminNote = trim($_POST['admin_note'] ?? '');

        try {
            if (empty($adminNote)) {
                throw new Exception('Vui lòng nhập lý do từ chối.');
            }

            // Lấy thông tin yêu cầu
            $transfer = $this->transferModel->getDetailById($transferId);
            if (!$transfer) {
                throw new Exception('Không tìm thấy yêu cầu chuyển ca.');
            }

            if ($transfer['status'] !== 'pending') {
                throw new Exception('Yêu cầu này đã được xử lý.');
            }

            // Từ chối yêu cầu
            $this->transferModel->reject($transferId, $admin['id'], $adminNote);

            // Ghi log
            $this->transferLogModel->createLog(
                $transferId,
                'rejected',
                $admin['id'],
                "Admin từ chối chuyển ca. Lý do: $adminNote"
            );

            $_SESSION['success'] = 'Đã từ chối yêu cầu chuyển ca.';

        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts/transfers/list');
    }

    /**
     * Xóa yêu cầu chuyển ca (Admin)
     */
    public function transferDelete($transferId)
    {
        $this->requireAdmin();

        try {
            $transfer = $this->transferModel->find($transferId);
            if (!$transfer) {
                throw new Exception('Không tìm thấy yêu cầu chuyển ca.');
            }

            // Xóa logs trước (nếu có foreign key cascade thì không cần)
            // $this->transferLogModel->deleteByTransferId($transferId);

            // Xóa yêu cầu chuyển ca
            $this->transferModel->delete($transferId);

            $_SESSION['success'] = 'Đã xóa yêu cầu chuyển ca thành công.';

        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts/transfers/list');
    }

    /**
     * Xem chi tiết yêu cầu và log
     */
    public function transferDetail($transferId)
    {
        $user = $this->getUser();
        
        $transfer = $this->transferModel->getDetailById($transferId);
        if (!$transfer) {
            $_SESSION['error'] = 'Không tìm thấy yêu cầu chuyển ca.';
            $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts');
            return;
        }

        // Kiểm tra quyền xem: admin hoặc người liên quan
        if ($user['role'] !== 'admin' && 
            (int)$transfer['from_staff_id'] !== (int)$user['id'] && 
            (int)$transfer['to_staff_id'] !== (int)$user['id']) {
            $_SESSION['error'] = 'Bạn không có quyền xem yêu cầu này.';
            $this->redirect('/Quan_ly_trung_tam/public/teaching-shifts');
            return;
        }

        // Lấy log
        $logs = $this->transferLogModel->getByTransferId($transferId);

        $this->view('teaching_shifts/transfer_detail', [
            'transfer' => $transfer,
            'logs' => $logs,
            'user' => $user
        ]);
    }
}
