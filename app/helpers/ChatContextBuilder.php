<?php

/**
 * ChatContextBuilder
 * Thực thi các "tool functions" mà AI yêu cầu, có phân quyền theo role
 *
 * Tools:
 *  - get_dashboard_stats    : Thống kê tổng quan
 *  - get_revenue_stats      : Doanh thu theo kỳ
 *  - search_students        : Tìm học viên
 *  - get_recent_reports     : Báo cáo học viên đến trung tâm
 *  - get_courses_list       : Danh sách khóa học từ DB
 */
class ChatContextBuilder
{
    private $db;
    private int $userId;
    private string $userRole;

    public function __construct($db, int $userId, string $userRole)
    {
        $this->db       = $db;
        $this->userId   = $userId;
        $this->userRole = $userRole;
    }

    /**
     * Điểm vào duy nhất: thực thi function theo tên
     */
    public function execute(string $funcName, array $args): array
    {
        return match ($funcName) {
            'get_dashboard_stats'         => $this->getDashboardStats(),
            'get_revenue_stats'           => $this->getRevenueStats($args),
            'search_students'             => $this->searchStudents((string)($args['query'] ?? '')),
            'get_recent_reports'          => $this->getRecentReports((string)($args['date'] ?? '')),
            'get_courses_list'            => $this->getCoursesList(),
            'get_certificates_stats'      => $this->getCertificatesStats(),
            'get_staff_list'              => $this->getStaffList(),
            'get_completion_slips_stats'  => $this->getCompletionSlipsStats(),
            default                       => ['loi' => 'Hàm không tồn tại: ' . $funcName],
        };
    }

    // ----------------------------------------------------------------
    // Tool 1: Thống kê tổng quan
    // ----------------------------------------------------------------
    private function getDashboardStats(): array
    {
        // Số học viên theo trạng thái
        $studentRows = $this->db->fetchAll(
            "SELECT status, COUNT(*) as cnt FROM students GROUP BY status"
        );

        $students = [];
        $statusMap = ['studying' => 'Đang học', 'completed' => 'Đã hoàn thành', 'dropped' => 'Đã nghỉ'];
        foreach ($studentRows as $r) {
            $students[$statusMap[$r['status']] ?? $r['status']] = (int)$r['cnt'];
        }

        // Doanh thu hôm nay
        [$todayRev] = $this->revenueQuery(
            "DATE(payment_date) = CURDATE()",
            []
        );

        // Doanh thu tháng này
        [$monthRev] = $this->revenueQuery(
            "MONTH(payment_date) = MONTH(CURDATE()) AND YEAR(payment_date) = YEAR(CURDATE())",
            []
        );

        // Báo cáo hôm nay
        if ($this->userRole === 'admin') {
            $rptRow = $this->db->fetch(
                "SELECT COUNT(*) as cnt, COALESCE(SUM(total_visitors),0) as vis, COALESCE(SUM(total_registered),0) as reg
                 FROM reports WHERE DATE(report_date) = CURDATE()"
            );
        } else {
            $rptRow = $this->db->fetch(
                "SELECT COUNT(*) as cnt, COALESCE(SUM(total_visitors),0) as vis, COALESCE(SUM(total_registered),0) as reg
                 FROM reports WHERE DATE(report_date) = CURDATE() AND staff_id = ?",
                [$this->userId]
            );
        }

        return [
            'hoc_vien'              => $students,
            'doanh_thu_hom_nay'     => $this->formatMoney($todayRev['tong'] ?? 0)
                                        . ' (' . ($todayRev['so_gd'] ?? 0) . ' giao dịch)',
            'doanh_thu_thang_nay'   => $this->formatMoney($monthRev['tong'] ?? 0)
                                        . ' (' . ($monthRev['so_gd'] ?? 0) . ' giao dịch)',
            'bao_cao_hom_nay'       => [
                'so_bao_cao'   => (int)($rptRow['cnt'] ?? 0),
                'tong_khach'   => (int)($rptRow['vis'] ?? 0),
                'tong_dang_ky' => (int)($rptRow['reg'] ?? 0),
            ],
            'pham_vi' => $this->userRole === 'admin' ? 'Toàn trung tâm' : 'Dữ liệu của bạn',
        ];
    }

    // ----------------------------------------------------------------
    // Tool 2: Doanh thu theo kỳ
    // ----------------------------------------------------------------
    private function getRevenueStats(array $args): array
    {
        $period = $args['period'] ?? 'month';
        $month  = (int)($args['month'] ?? date('n'));
        $year   = (int)($args['year']  ?? date('Y'));

        // Xây dựng điều kiện ngày
        switch ($period) {
            case 'today':
                $dateWhere = "DATE(payment_date) = CURDATE()";
                $dateParams = [];
                $label = 'Hôm nay (' . date('d/m/Y') . ')';
                break;
            case 'week':
                $dateWhere = "YEARWEEK(payment_date, 1) = YEARWEEK(CURDATE(), 1)";
                $dateParams = [];
                $label = 'Tuần này';
                break;
            case 'year':
                $dateWhere = "YEAR(payment_date) = ?";
                $dateParams = [$year];
                $label = "Năm $year";
                break;
            default: // month
                $dateWhere = "MONTH(payment_date) = ? AND YEAR(payment_date) = ?";
                $dateParams = [$month, $year];
                $label = "Tháng $month/$year";
        }

        [$total, $breakdown] = $this->revenueQuery($dateWhere, $dateParams, true);

        return [
            'ky'                    => $label,
            'tong_doanh_thu'        => $this->formatMoney($total['tong'] ?? 0),
            'so_giao_dich'          => (int)($total['so_gd'] ?? 0),
            'phan_loai_hinh_thuc'   => $breakdown,
            'pham_vi'               => $this->userRole === 'admin' ? 'Toàn trung tâm' : 'Chỉ dữ liệu của bạn',
        ];
    }

    // ----------------------------------------------------------------
    // Tool 3: Tìm học viên
    // ----------------------------------------------------------------
    private function searchStudents(string $query): array
    {
        $query = trim($query);
        if (mb_strlen($query) < 2) {
            return ['loi' => 'Từ khóa quá ngắn, cần ít nhất 2 ký tự.'];
        }

        $search = '%' . $query . '%';
        $rows = $this->db->fetchAll(
            "SELECT s.full_name, s.phone, s.email, s.status,
                    s.enrollment_date, s.completion_date,
                    c.course_name, u.full_name AS instructor_name
             FROM students s
             LEFT JOIN courses c ON s.course_id = c.id
             LEFT JOIN users u ON s.instructor_id = u.id
             WHERE s.full_name LIKE ? OR s.phone LIKE ?
             ORDER BY s.created_at DESC
             LIMIT 10",
            [$search, $search]
        );

        if (empty($rows)) {
            return ['ket_qua' => "Không tìm thấy học viên nào với từ khóa: \"$query\""];
        }

        $statusMap = ['studying' => 'Đang học', 'completed' => 'Đã hoàn thành', 'dropped' => 'Đã nghỉ'];
        $list = [];

        foreach ($rows as $s) {
            $list[] = [
                'ho_ten'        => $s['full_name'],
                'so_dien_thoai' => $s['phone'] ?: 'Chưa có',
                'khoa_hoc'      => $s['course_name'] ?: 'Chưa đăng ký',
                'trang_thai'    => $statusMap[$s['status']] ?? $s['status'],
                'giao_vien'     => $s['instructor_name'] ?: 'Chưa phân công',
                'ngay_nhap_hoc' => $s['enrollment_date']
                    ? date('d/m/Y', strtotime($s['enrollment_date'])) : 'Chưa có',
            ];
        }

        return [
            'tong_ket_qua' => count($list),
            'hoc_vien'     => $list,
        ];
    }

    // ----------------------------------------------------------------
    // Tool 4: Báo cáo học viên đến
    // ----------------------------------------------------------------
    private function getRecentReports(string $date): array
    {
        // Validate / mặc định hôm nay
        if ($date === '' || $date === 'today') {
            $dateStr = date('Y-m-d');
        } else {
            $d = DateTime::createFromFormat('Y-m-d', $date);
            $dateStr = ($d && $d->format('Y-m-d') === $date) ? $date : date('Y-m-d');
        }

        $displayDate = date('d/m/Y', strtotime($dateStr));

        if ($this->userRole === 'admin') {
            $rows = $this->db->fetchAll(
                "SELECT r.report_time, r.total_visitors, r.total_registered, r.notes,
                        u.full_name AS staff_name
                 FROM reports r
                 JOIN users u ON r.staff_id = u.id
                 WHERE r.report_date = ?
                 ORDER BY r.report_time ASC",
                [$dateStr]
            );
        } else {
            $rows = $this->db->fetchAll(
                "SELECT r.report_time, r.total_visitors, r.total_registered, r.notes,
                        u.full_name AS staff_name
                 FROM reports r
                 JOIN users u ON r.staff_id = u.id
                 WHERE r.report_date = ? AND r.staff_id = ?
                 ORDER BY r.report_time ASC",
                [$dateStr, $this->userId]
            );
        }

        if (empty($rows)) {
            return ['ngay' => $displayDate, 'ket_qua' => "Không có báo cáo nào ngày $displayDate"];
        }

        $totalVis = 0;
        $totalReg = 0;
        $details  = [];

        foreach ($rows as $r) {
            $totalVis += (int)$r['total_visitors'];
            $totalReg += (int)$r['total_registered'];
            $details[] = [
                'gio'        => $r['report_time'],
                'nhan_vien'  => $r['staff_name'],
                'khach_den'  => (int)$r['total_visitors'],
                'dang_ky'    => (int)$r['total_registered'],
                'ghi_chu'    => $r['notes'] ?: '',
            ];
        }

        return [
            'ngay'           => $displayDate,
            'tong_so_bao_cao' => count($rows),
            'tong_khach_den' => $totalVis,
            'tong_dang_ky'   => $totalReg,
            'ty_le_dang_ky'  => $totalVis > 0
                ? round($totalReg / $totalVis * 100, 1) . '%' : '0%',
            'chi_tiet'       => $details,
            'pham_vi'        => $this->userRole === 'admin' ? 'Toàn trung tâm' : 'Chỉ báo cáo của bạn',
        ];
    }

    // ----------------------------------------------------------------
    // Tool 5: Danh sách khóa học từ DB
    // ----------------------------------------------------------------
    private function getCoursesList(): array
    {
        $rows = $this->db->fetchAll(
            "SELECT course_code, course_name, description, price, duration_hours, status
             FROM courses
             ORDER BY status DESC, course_name ASC"
        );

        if (empty($rows)) {
            return ['ket_qua' => 'Không có khóa học nào trong hệ thống.'];
        }

        $active = 0;
        $list   = [];

        foreach ($rows as $c) {
            if ($c['status'] === 'active') {
                $active++;
            }
            $list[] = [
                'ma_khoa'    => $c['course_code'] ?: '',
                'ten_khoa'   => $c['course_name'],
                'mo_ta'      => $c['description']    ?: '',
                'hoc_phi'    => $this->formatMoney((float)$c['price']),
                'thoi_luong' => $c['duration_hours'] ? $c['duration_hours'] . ' giờ' : 'Chưa có',
                'trang_thai' => $c['status'] === 'active' ? 'Đang mở' : 'Tạm đóng',
            ];
        }

        return [
            'tong_khoa_hoc'    => count($rows),
            'dang_hoat_dong'   => $active,
            'danh_sach'        => $list,
        ];
    }

    // ----------------------------------------------------------------
    // Tool 6: Thống kê chứng nhận (chỉ admin)
    // ----------------------------------------------------------------
    private function getCertificatesStats(): array
    {
        if ($this->userRole !== 'admin') {
            return ['loi' => 'Bạn không có quyền xem thông tin chứng nhận.'];
        }

        $rows = $this->db->fetchAll(
            "SELECT approval_status, COUNT(*) as cnt FROM certificates GROUP BY approval_status"
        );

        $statusMap = ['pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'cancelled' => 'Đã hủy'];
        $stats = ['Chờ duyệt' => 0, 'Đã duyệt' => 0, 'Đã hủy' => 0];
        foreach ($rows as $r) {
            $label = $statusMap[$r['approval_status']] ?? $r['approval_status'];
            $stats[$label] = (int)$r['cnt'];
        }

        $recent = $this->db->fetchAll(
            "SELECT student_name, subject, approval_status, created_at
             FROM certificates ORDER BY created_at DESC LIMIT 5"
        );
        $recentList = [];
        foreach ($recent as $r) {
            $recentList[] = [
                'hoc_vien'   => $r['student_name'],
                'mon_hoc'    => $r['subject'],
                'trang_thai' => $statusMap[$r['approval_status']] ?? $r['approval_status'],
                'ngay_tao'   => date('d/m/Y', strtotime($r['created_at'])),
            ];
        }

        return [
            'thong_ke' => $stats,
            'tong'     => array_sum($stats),
            'gan_day'  => $recentList,
        ];
    }

    // ----------------------------------------------------------------
    // Tool 7: Danh sách nhân viên (chỉ admin)
    // ----------------------------------------------------------------
    private function getStaffList(): array
    {
        if ($this->userRole !== 'admin') {
            return ['loi' => 'Bạn không có quyền xem danh sách nhân viên.'];
        }

        $rows = $this->db->fetchAll(
            "SELECT full_name, email, phone, role, status FROM users ORDER BY role DESC, full_name ASC"
        );

        $list        = [];
        $activeCount = 0;
        foreach ($rows as $u) {
            if ($u['status'] === 'active') {
                $activeCount++;
            }
            $list[] = [
                'ho_ten'     => $u['full_name'],
                'email'      => $u['email'] ?: 'Chưa có',
                'sdt'        => $u['phone'] ?: 'Chưa có',
                'vai_tro'    => $u['role'] === 'admin' ? 'Quản trị viên' : 'Nhân viên',
                'trang_thai' => $u['status'] === 'active' ? 'Đang hoạt động' : 'Đã khóa',
            ];
        }

        return [
            'tong_tai_khoan'  => count($rows),
            'dang_hoat_dong'  => $activeCount,
            'danh_sach'       => $list,
        ];
    }

    // ----------------------------------------------------------------
    // Tool 8: Thống kê phiếu hoàn thành (chỉ admin)
    // ----------------------------------------------------------------
    private function getCompletionSlipsStats(): array
    {
        if ($this->userRole !== 'admin') {
            return ['loi' => 'Bạn không có quyền xem phiếu hoàn thành.'];
        }

        $total   = $this->db->fetch("SELECT COUNT(*) as cnt FROM completion_slips");
        $monthly = $this->db->fetch(
            "SELECT COUNT(*) as cnt FROM completion_slips
             WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())"
        );
        $recent = $this->db->fetchAll(
            "SELECT cs.student_name, cs.teacher_name, c.course_name, cs.created_at
             FROM completion_slips cs
             LEFT JOIN courses c ON cs.course_id = c.id
             ORDER BY cs.created_at DESC LIMIT 5"
        );

        $recentList = [];
        foreach ($recent as $r) {
            $recentList[] = [
                'hoc_vien'  => $r['student_name'],
                'giao_vien' => $r['teacher_name'] ?: 'Chưa có',
                'khoa_hoc'  => $r['course_name']  ?: 'Chưa có',
                'ngay'      => date('d/m/Y', strtotime($r['created_at'])),
            ];
        }

        return [
            'tong_phieu'  => (int)($total['cnt']   ?? 0),
            'thang_nay'   => (int)($monthly['cnt'] ?? 0),
            'gan_day'     => $recentList,
        ];
    }

    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------

    /**
     * Query doanh thu với date condition, có filter theo role
     * Trả về [total_row, breakdown_array]
     */
    private function revenueQuery(string $dateWhere, array $dateParams, bool $withBreakdown = false): array
    {
        // Thêm filter staff nếu không phải admin
        $staffWhere  = $this->userRole === 'admin' ? '' : 'AND staff_id = ?';
        $staffParams = $this->userRole === 'admin' ? [] : [$this->userId];

        $allParams = array_merge($dateParams, $staffParams);

        $totalRow = $this->db->fetch(
            "SELECT COUNT(*) as so_gd, COALESCE(SUM(amount), 0) as tong
             FROM revenue_reports
             WHERE $dateWhere $staffWhere",
            $allParams
        );

        if (!$withBreakdown) {
            return [$totalRow, []];
        }

        $breakdownRows = $this->db->fetchAll(
            "SELECT transfer_type, COALESCE(SUM(amount), 0) as tong
             FROM revenue_reports
             WHERE $dateWhere $staffWhere
             GROUP BY transfer_type",
            $allParams
        );

        $typeMap = [
            'cash'                 => 'Tiền mặt',
            'account_co_nhi'       => 'TK Cô Nhi',
            'account_thay_hien'    => 'TK Thầy Hiền',
            'account_company'      => 'TK Công ty',
        ];

        $breakdown = [];
        foreach ($breakdownRows as $r) {
            $label            = $typeMap[$r['transfer_type']] ?? $r['transfer_type'];
            $breakdown[$label] = $this->formatMoney((float)$r['tong']);
        }

        return [$totalRow, $breakdown];
    }

    private function formatMoney(float $amount): string
    {
        return number_format($amount, 0, ',', '.') . 'đ';
    }
}
