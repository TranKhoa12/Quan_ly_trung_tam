<?php
require_once __DIR__ . '/../layouts/main.php';

// Page content
ob_start();
?>

<?php
$headerButton = '';
$headerTitle = 'Báo cáo đến trung tâm';
$headerDesc = 'Quản lý và theo dõi số lượng khách hàng đến trung tâm';

if (isset($userRole)) {
    if ($userRole === 'staff') {
        $headerTitle = 'Báo cáo công việc của tôi';
        $headerDesc = 'Xem lại các báo cáo bạn đã thực hiện trong ngày';
        $headerButton = '
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-warning" onclick="createEmptyReport()">
                    <i class="fas fa-file-alt me-2"></i>Báo cáo rỗng
                </button>
                <a href="/Quan_ly_trung_tam/public/reports/create" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Tạo báo cáo mới
                </a>
            </div>';
    } elseif ($userRole === 'admin') {
        $headerButton = '
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-warning" onclick="createEmptyReport()">
                    <i class="fas fa-file-alt me-2"></i>Báo cáo rỗng
                </button>
                <a href="/Quan_ly_trung_tam/public/reports/create" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Tạo báo cáo mới
                </a>
            </div>';
    }
}
?>

<?= pageHeader(
    $headerTitle, 
    $headerDesc, 
    $headerButton
) ?>

<div class="p-3">
    <!-- Filter and Search - Chỉ hiển thị cho Admin -->
    <?php if (isset($userRole) && $userRole === 'admin'): ?>
    <div class="stats-card mb-4">
        <div class="card-body">
            <form method="GET" action="/Quan_ly_trung_tam/public/reports">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="fas fa-calendar text-primary"></i> Từ ngày
                        </label>
                        <input type="date" class="form-control" name="from_date" 
                               value="<?= $_GET['from_date'] ?? '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="fas fa-calendar text-primary"></i> Đến ngày
                        </label>
                        <input type="date" class="form-control" name="to_date" 
                               value="<?= $_GET['to_date'] ?? '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="fas fa-user text-primary"></i> Nhân viên
                        </label>
                        <select class="form-select" name="staff_id">
                            <option value="">Tất cả nhân viên</option>
                            <?php if (!empty($staff)): ?>
                                <?php foreach ($staff as $s): ?>
                                    <option value="<?= $s['id'] ?>" 
                                            <?= (isset($_GET['staff_id']) && $_GET['staff_id'] == $s['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($s['full_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Tìm kiếm
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Summary Statistics - Chỉ hiển thị cho Admin -->
    <?php if (isset($userRole) && $userRole === 'admin'): ?>
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <?= statsCard(
                'fas fa-calendar-day', 
                'Tổng báo cáo', 
                count($reports), 
                'Số báo cáo trong khoảng thời gian',
                'primary'
            ) ?>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <?php 
            $total_visitors = array_sum(array_column($reports, 'total_visitors'));
            ?>
            <?= statsCard(
                'fas fa-users', 
                'Tổng khách đến', 
                $total_visitors, 
                'Tổng số lượng khách hàng',
                'info'
            ) ?>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <?php 
            $total_registered = array_sum(array_column($reports, 'total_registered'));
            ?>
            <?= statsCard(
                'fas fa-check-circle', 
                'Tổng khách chốt', 
                $total_registered, 
                'Số khách hàng đã chốt',
                'success'
            ) ?>
        </div>
        
        <div class="col-md-3 col-sm-6">
            <?php 
            $conversion_rate = $total_visitors > 0 ? ($total_registered / $total_visitors * 100) : 0;
            $rate_color = $conversion_rate >= 50 ? 'success' : ($conversion_rate >= 20 ? 'warning' : 'danger');
            ?>
            <?= statsCard(
                'fas fa-percentage', 
                'Tỷ lệ chốt', 
                number_format($conversion_rate, 1) . '%', 
                'Tỷ lệ chốt trung bình',
                $rate_color
            ) ?>
        </div>
    </div>
    <?php else: ?>
    <!-- Thông tin cho Nhân viên -->
    <div class="stats-card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="card-title mb-0">
                    <i class="fas fa-calendar-day text-primary me-2"></i>
                    Báo cáo của bạn hôm nay (<?= date('d/m/Y') ?>)
                </h6>
                <span class="badge bg-info"><?= count($reports) ?> báo cáo</span>
            </div>
            
            <?php if (count($reports) > 0): ?>
                <div class="row g-3">
                    <div class="col-md-4">
                        <?php 
                        $total_visitors = array_sum(array_column($reports, 'total_visitors'));
                        ?>
                        <?= statsCard(
                            'fas fa-users', 
                            'Khách đến', 
                            $total_visitors, 
                            'Tổng khách bạn đã báo cáo',
                            'info'
                        ) ?>
                    </div>
                    <div class="col-md-4">
                        <?php 
                        $total_registered = array_sum(array_column($reports, 'total_registered'));
                        ?>
                        <?= statsCard(
                            'fas fa-check-circle', 
                            'Khách chốt', 
                            $total_registered, 
                            'Tổng khách đã chốt',
                            'success'
                        ) ?>
                    </div>
                    <div class="col-md-4">
                        <?php 
                        $conversion_rate = $total_visitors > 0 ? ($total_registered / $total_visitors * 100) : 0;
                        $rate_color = $conversion_rate >= 50 ? 'success' : ($conversion_rate >= 20 ? 'warning' : 'danger');
                        ?>
                        <?= statsCard(
                            'fas fa-percentage', 
                            'Tỷ lệ chốt của bạn', 
                            number_format($conversion_rate, 1) . '%', 
                            'Hiệu quả làm việc hôm nay',
                            $rate_color
                        ) ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-calendar-check text-muted fa-3x mb-3"></i>
                    <h6 class="text-muted">Bạn chưa có báo cáo nào hôm nay</h6>
                    <p class="text-muted mb-3">Tạo báo cáo đầu tiên để ghi nhận công việc của bạn</p>
                    <a href="/Quan_ly_trung_tam/public/reports/create" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Tạo báo cáo ngay
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Reports Table -->
    <div class="stats-card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="card-title mb-0">
                    <i class="fas fa-list text-primary me-2"></i>
                    <?php if (isset($userRole) && $userRole === 'staff'): ?>
                        Chi tiết báo cáo hôm nay
                    <?php else: ?>
                        Danh sách báo cáo
                    <?php endif; ?>
                </h6>
                <span class="badge bg-primary"><?= count($reports) ?> báo cáo</span>
            </div>
            
            <?php if (!empty($reports)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Ngày báo cáo</th>
                                <?php if (isset($userRole) && $userRole === 'admin'): ?>
                                <th>Nhân viên</th>
                                <?php endif; ?>
                                <th>Số lượng đến</th>
                                <th>Số lượng chốt</th>
                                <?php if (isset($userRole) && $userRole === 'admin'): ?>
                                <th>Tỷ lệ chốt</th>
                                <?php endif; ?>
                                <th>Ghi chú</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reports as $report): ?>
                                <tr>
                                    <td>
                                        <div>
                                            <span class="fw-semibold"><?= date('d/m/Y', strtotime($report['report_date'])) ?></span>
                                            <br><small class="text-muted"><?= date('H:i', strtotime($report['report_time'])) ?></small>
                                        </div>
                                    </td>
                                    <?php if (isset($userRole) && $userRole === 'admin'): ?>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-user-circle text-muted me-2"></i>
                                            <?= htmlspecialchars($report['staff_name'] ?? 'N/A') ?>
                                        </div>
                                    </td>
                                    <?php endif; ?>
                                    <td>
                                        <span class="badge bg-info fs-6"><?= $report['total_visitors'] ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success fs-6"><?= $report['total_registered'] ?></span>
                                    </td>
                                    <?php if (isset($userRole) && $userRole === 'admin'): ?>
                                    <td>
                                        <?php 
                                        $rate = $report['total_visitors'] > 0 ? 
                                            ($report['total_registered'] / $report['total_visitors'] * 100) : 0;
                                        $color = $rate >= 50 ? 'success' : ($rate >= 20 ? 'warning' : 'danger');
                                        ?>
                                        <span class="badge bg-<?= $color ?> fs-6"><?= number_format($rate, 1) ?>%</span>
                                    </td>
                                    <?php endif; ?>
                                    <td>
                                        <?php if (!empty($report['notes'])): ?>
                                            <span class="text-truncate d-inline-block" style="max-width: 150px;" 
                                                  title="<?= htmlspecialchars($report['notes']) ?>">
                                                <?= htmlspecialchars(substr($report['notes'], 0, 50)) ?>
                                                <?= strlen($report['notes']) > 50 ? '...' : '' ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">Không có</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (isset($userRole) && $userRole === 'admin'): ?>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="viewReportDetails(<?= $report['id'] ?>)" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a href="/Quan_ly_trung_tam/public/reports/<?= $report['id'] ?>/edit" 
                                               class="btn btn-sm btn-outline-warning" title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteReport(<?= $report['id'] ?>)" title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        <?php else: ?>
                                        <!-- Nhân viên chỉ xem chi tiết -->
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="viewReportDetails(<?= $report['id'] ?>)" title="Xem chi tiết">
                                            <i class="fas fa-eye me-1"></i>Xem
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if (count($reports) === 0): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-search text-muted fa-3x mb-3"></i>
                        <p class="text-muted">Không tìm thấy báo cáo nào phù hợp với điều kiện tìm kiếm</p>
                        <a href="/Quan_ly_trung_tam/public/reports" class="btn btn-outline-primary">
                            <i class="fas fa-refresh me-2"></i>Xem tất cả
                        </a>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-chart-line text-muted fa-4x mb-3"></i>
                    <h5 class="text-muted">Chưa có báo cáo nào</h5>
                    <p class="text-muted">Bắt đầu tạo báo cáo đầu tiên để theo dõi số lượng khách hàng đến trung tâm</p>
                    <a href="/Quan_ly_trung_tam/public/reports/create" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Tạo báo cáo đầu tiên
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal chi tiết báo cáo -->
<div class="modal fade" id="reportDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-list-alt text-primary me-2"></i>
                    Chi tiết báo cáo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="reportDetailContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                    <p class="mt-2 text-muted">Đang tải thông tin...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Đóng
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal xác nhận báo cáo rỗng -->
<div class="modal fade" id="emptyReportModal" tabindex="-1" aria-labelledby="emptyReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="emptyReportModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Xác nhận báo cáo rỗng
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-file-alt text-warning" style="font-size: 3rem;"></i>
                </div>
                <p class="text-center mb-3">
                    Bạn có chắc chắn muốn tạo <strong>báo cáo rỗng</strong> cho ngày hôm nay?
                </p>
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle me-2"></i>Thông tin báo cáo sẽ được tạo:</h6>
                    <ul class="mb-0">
                        <li><strong>Ngày báo cáo:</strong> <?= date('d/m/Y') ?></li>
                        <li><strong>Số lượng đến:</strong> 0 người</li>
                        <li><strong>Số lượng chốt:</strong> 0 khách hàng</li>
                        <li><strong>Chi tiết khách hàng:</strong> Không có</li>
                        <li><strong>Ghi chú:</strong> Báo cáo rỗng - Không có khách hàng đến</li>
                    </ul>
                </div>
                <p class="text-muted small">
                    <i class="fas fa-lightbulb me-1"></i>
                    Báo cáo này sẽ được lưu vào cơ sở dữ liệu để đảm bảo tính liên tục của dữ liệu.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Hủy bỏ
                </button>
                <button type="button" class="btn btn-warning" onclick="confirmEmptyReport()" data-bs-dismiss="modal">
                    <i class="fas fa-check me-2"></i>Xác nhận tạo báo cáo rỗng
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Custom JavaScript
$customJs = '
function createEmptyReport() {
    // Hiển thị modal xác nhận
    const modal = new bootstrap.Modal(document.getElementById("emptyReportModal"));
    modal.show();
}

function confirmEmptyReport() {
    // Lấy thông tin user hiện tại
  const userRole = "' . (isset($userRole) ? htmlspecialchars($userRole, ENT_QUOTES) : 'staff') . '";
    const userId = "' . (isset($user) ? htmlspecialchars($user["id"], ENT_QUOTES) : '1') . '";

    
    // Tạo form để submit báo cáo rỗng
    const form = document.createElement("form");
    form.method = "POST";
    form.action = "/Quan_ly_trung_tam/public/reports";
    
    // Thêm các hidden input
    const inputs = [
        {name: "report_date", value: new Date().toISOString().split("T")[0]},
        {name: "report_time", value: new Date().toTimeString().split(" ")[0]},
        {name: "staff_id", value: userId},
        {name: "total_visitors", value: "0"},
        {name: "total_registered", value: "0"},
        {name: "empty_report", value: "1"},
        {name: "notes", value: "Báo cáo rỗng - Không có khách hàng đến trung tâm trong ngày " + new Date().toISOString().split("T")[0]}
    ];
    
    inputs.forEach(input => {
        const hiddenInput = document.createElement("input");
        hiddenInput.type = "hidden";
        hiddenInput.name = input.name;
        hiddenInput.value = input.value;
        form.appendChild(hiddenInput);
    });
    
    // Debug: In ra console để kiểm tra
    console.log("Submitting empty report with data:", inputs);
    
    document.body.appendChild(form);
    form.submit();
}

function deleteReport(reportId) {
    if (confirm("Bạn có chắc chắn muốn xóa báo cáo này?")) {
        // Create form and submit
        const form = document.createElement("form");
        form.method = "POST";
        form.action = "/Quan_ly_trung_tam/public/reports/" + reportId + "/delete";
        
        const csrfToken = document.createElement("input");
        csrfToken.type = "hidden";
        csrfToken.name = "_token";
        csrfToken.value = "csrf_token_here"; // Add CSRF token if needed
        
        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
    }
}

function viewReportDetails(reportId) {
    // Hiển thị modal
    const modal = new bootstrap.Modal(document.getElementById("reportDetailModal"));
    modal.show();
    
    // Reset nội dung modal về loading state
    document.getElementById("reportDetailContent").innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Đang tải...</span>
            </div>
            <p class="mt-2 text-muted">Đang tải thông tin...</p>
        </div>
    `;
    
    // Gọi API để lấy chi tiết báo cáo
    fetch(`/Quan_ly_trung_tam/public/api/reports/${reportId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderReportDetails(data.data);
            } else {
                showError(data.message || "Không thể tải thông tin báo cáo");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            showError("Đã xảy ra lỗi khi tải thông tin");
        });
}

function renderReportDetails(data) {
    const report = data.report;
    const customers = data.customers;
    
    // Tính toán thống kê
    const conversionRate = report.total_visitors > 0 ? 
        (report.total_registered / report.total_visitors * 100) : 0;
    const rateColor = conversionRate >= 50 ? "success" : (conversionRate >= 20 ? "warning" : "danger");
    
    let html = `
        <!-- Thông tin báo cáo -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="fas fa-calendar text-primary me-2"></i>
                            Thông tin báo cáo
                        </h6>
                        <p class="mb-1"><strong>Ngày:</strong> ${formatDate(report.report_date)}</p>
                        <p class="mb-1"><strong>Giờ:</strong> ${formatTime(report.report_time)}</p>
                        <p class="mb-0"><strong>Nhân viên:</strong> ${report.staff_name || "N/A"}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="fas fa-chart-bar text-success me-2"></i>
                            Thống kê
                        </h6>
                        <p class="mb-1"><strong>Khách đến:</strong> <span class="badge bg-info">${report.total_visitors}</span></p>
                        <p class="mb-1"><strong>Khách chốt:</strong> <span class="badge bg-success">${report.total_registered}</span></p>
                        <p class="mb-0"><strong>Tỷ lệ chốt:</strong> <span class="badge bg-${rateColor}">${conversionRate.toFixed(1)}%</span></p>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Ghi chú nếu có
    if (report.notes) {
        html += `
            <div class="card mb-4">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="fas fa-sticky-note text-warning me-2"></i>
                        Ghi chú
                    </h6>
                    <p class="mb-0">${report.notes.replace(/\\n/g, "<br>")}</p>
                </div>
            </div>
        `;
    }
    
    // Danh sách khách hàng
    html += `
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="fas fa-users text-primary me-2"></i>
                    Danh sách khách hàng (${customers.length} người)
                </h6>
    `;
    
    if (customers.length > 0) {
        html += `
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Họ tên</th>
                            <th>Số điện thoại</th>
                            <th>Khóa học</th>
                            <th>Trạng thái</th>
                            <th>Đăng ký</th>
                            <th>Thanh toán</th>
                            <th>Ghi chú</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        customers.forEach(customer => {
            const statusLabels = {
                "new": "Mới",
                "contacted": "Đã liên hệ", 
                "interested": "Quan tâm",
                "registered": "Đã đăng ký",
                "not_interested": "Không quan tâm"
            };
            
            const statusColors = {
                "new": "primary",
                "contacted": "info",
                "interested": "warning", 
                "registered": "success",
                "not_interested": "secondary"
            };
            
            const statusLabel = statusLabels[customer.status] || customer.status;
            const statusColor = statusColors[customer.status] || "secondary";
            
            html += `
                <tr>
                    <td>
                        <i class="fas fa-user-circle text-muted me-1"></i>
                        ${customer.full_name}
                    </td>
                    <td>
                        <a href="tel:${customer.phone}" class="text-decoration-none">
                            <i class="fas fa-phone text-success me-1"></i>
                            ${customer.phone}
                        </a>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark">
                            ${customer.course_name || "Chưa chọn"}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-${statusColor}">${statusLabel}</span>
                    </td>
                    <td>
                        ${customer.registration_status === "registered" ? 
                            "<i class=\"fas fa-check-circle text-success\"></i> Đã đăng ký" : 
                            "<i class=\"fas fa-minus-circle text-muted\"></i> Chưa đăng ký"
                        }
                    </td>
                    <td>
                        ${customer.payment_method ? 
                            `<span class="badge bg-info">${customer.payment_method}</span>` : 
                            "<span class=\"text-muted\">-</span>"
                        }
                    </td>
                    <td>
                        ${customer.notes ? 
                            `<span title="${customer.notes}">${customer.notes.length > 20 ? customer.notes.substring(0, 20) + "..." : customer.notes}</span>` : 
                            "<span class=\"text-muted\">-</span>"
                        }
                    </td>
                </tr>
            `;
        });
        
        html += `
                    </tbody>
                </table>
            </div>
        `;
    } else {
        html += `
            <div class="text-center py-4">
                <i class="fas fa-users text-muted fa-3x mb-3"></i>
                <h6 class="text-muted">Chưa có thông tin khách hàng</h6>
                <p class="text-muted">Báo cáo này chưa có dữ liệu chi tiết về khách hàng</p>
            </div>
        `;
    }
    
    html += `
            </div>
        </div>
    `;
    
    document.getElementById("reportDetailContent").innerHTML = html;
}

function showError(message) {
    document.getElementById("reportDetailContent").innerHTML = `
        <div class="text-center py-4">
            <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
            <h6 class="text-muted">Không thể tải thông tin</h6>
            <p class="text-muted">${message}</p>
        </div>
    `;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString("vi-VN");
}

function formatTime(timeString) {
    return timeString.substring(0, 5); // HH:MM
}
';

// Render layout
echo renderLayout('Báo cáo đến trung tâm', $content, 'reports', '', $customJs);
?>