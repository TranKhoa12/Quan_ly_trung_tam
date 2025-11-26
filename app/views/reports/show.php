<?php
require_once __DIR__ . '/../layouts/main.php';

// Page content
ob_start();

// Header for different roles
$headerTitle = 'Chi tiết báo cáo';
$headerDesc = 'Xem thông tin chi tiết báo cáo đến trung tâm';
$headerButton = '';

if (isset($userRole)) {
    if ($userRole === 'admin') {
        $headerButton = '<div class="btn-group">
            <a href="/Quan_ly_trung_tam/public/reports" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Quay lại
            </a>
            <a href="/Quan_ly_trung_tam/public/reports/' . ($report['id'] ?? '') . '/edit" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>Chỉnh sửa
            </a>
            <button type="button" class="btn btn-danger" onclick="deleteReport(' . ($report['id'] ?? '') . ')">
                <i class="fas fa-trash me-2"></i>Xóa
            </button>
        </div>';
    } else {
        $headerButton = '<a href="/Quan_ly_trung_tam/public/reports" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Quay lại
        </a>';
    }
}
?>

<?= pageHeader($headerTitle, $headerDesc, $headerButton) ?>

<style>
    /* Course Name Badge Styling */
    .course-name-badge {
        font-size: 0.75rem !important;
        line-height: 1.2 !important;
        padding: 0.25rem 0.5rem !important;
        border-radius: 0.375rem !important;
        cursor: help;
    }
    
    .course-name-badge:hover {
        background-color: #e9ecef !important;
        transform: scale(1.02);
        transition: all 0.2s ease-in-out;
    }
    
    /* Table styling for better course column */
    .table td {
        vertical-align: middle;
    }
    
    /* Responsive table adjustments */
    @media (max-width: 768px) {
        .course-name-badge {
            font-size: 0.7rem !important;
            max-width: 150px !important;
        }
        
        .table th:nth-child(3),
        .table td:nth-child(3) {
            max-width: 150px !important;
        }
    }
</style>

<div class="p-3">
    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php elseif ($report): ?>
        <!-- Report Information -->
        <div class="stats-card mb-4">
            <div class="card-body">
                <h6 class="card-title mb-4">
                    <i class="fas fa-info-circle text-primary me-2"></i>
                    Thông tin báo cáo
                </h6>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center p-3 bg-light rounded">
                            <i class="fas fa-calendar text-primary me-3 fa-lg"></i>
                            <div>
                                <h6 class="mb-1">Ngày báo cáo</h6>
                                <p class="mb-0 text-muted">
                                    <?= date('d/m/Y', strtotime($report['report_date'])) ?> 
                                    lúc <?= date('H:i', strtotime($report['report_time'])) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="d-flex align-items-center p-3 bg-light rounded">
                            <i class="fas fa-user text-primary me-3 fa-lg"></i>
                            <div>
                                <h6 class="mb-1">Nhân viên phụ trách</h6>
                                <p class="mb-0 text-muted"><?= htmlspecialchars($report['staff_name'] ?? 'N/A') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <?= statsCard(
                    'fas fa-users', 
                    'Tổng khách đến', 
                    $report['total_visitors'], 
                    'Số lượng khách hàng đến trung tâm',
                    'info'
                ) ?>
            </div>
            
            <div class="col-md-4">
                <?= statsCard(
                    'fas fa-check-circle', 
                    'Tổng khách chốt', 
                    $report['total_registered'], 
                    'Số khách hàng đã đăng ký',
                    'success'
                ) ?>
            </div>
            
            <?php if (isset($userRole) && $userRole === 'admin'): ?>
            <div class="col-md-4">
                <?php 
                $conversion_rate = $report['total_visitors'] > 0 ? 
                    ($report['total_registered'] / $report['total_visitors'] * 100) : 0;
                $rate_color = $conversion_rate >= 50 ? 'success' : ($conversion_rate >= 20 ? 'warning' : 'danger');
                ?>
                <?= statsCard(
                    'fas fa-percentage', 
                    'Tỷ lệ chốt', 
                    number_format($conversion_rate, 1) . '%', 
                    'Hiệu quả chuyển đổi',
                    $rate_color
                ) ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Notes -->
        <?php if (!empty($report['notes'])): ?>
        <div class="stats-card mb-4">
            <div class="card-body">
                <h6 class="card-title mb-3">
                    <i class="fas fa-sticky-note text-primary me-2"></i>
                    Khóa học/ Ghi chú
                </h6>
                <div class="p-3 bg-light rounded">
                    <p class="mb-0"><?= nl2br(htmlspecialchars($report['notes'])) ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Customer Details -->
        <div class="stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-users text-primary me-2"></i>
                        Chi tiết khách hàng
                    </h6>
                    <span class="badge bg-primary"><?= count($customers) ?> khách hàng</span>
                </div>
                
                <?php if (!empty($customers)): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th style="min-width: 120px;">Họ tên</th>
                                    <th style="min-width: 120px;">Số điện thoại</th>
                                    <th style="max-width: 250px; width: 250px;">Khóa học quan tâm</th>
                                    <th style="min-width: 100px;">Trạng thái</th>
                                    <th style="width: 80px; text-align: center;">Đăng ký</th>
                                    <th style="min-width: 120px;">Phương thức thanh toán</th>
                                    <th style="min-width: 100px;">Ghi chú</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customers as $customer): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-user-circle text-muted me-2"></i>
                                                <?= htmlspecialchars($customer['full_name']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="tel:<?= $customer['phone'] ?>" class="text-decoration-none">
                                                <i class="fas fa-phone text-success me-1"></i>
                                                <?= htmlspecialchars($customer['phone']) ?>
                                            </a>
                                        </td>
                                        <td style="max-width: 250px;">
                                            <?php 
                                            $courseName = $customer['course_name'] ?? 'Chưa chọn';
                                            $displayName = strlen($courseName) > 40 ? substr($courseName, 0, 37) . '...' : $courseName;
                                            ?>
                                            <span class="badge bg-light text-dark course-name-badge" 
                                                  title="<?= htmlspecialchars($courseName) ?>"
                                                  style="max-width: 100%; white-space: normal; word-wrap: break-word; 
                                                         text-overflow: ellipsis; overflow: hidden; display: inline-block;">
                                                <?= htmlspecialchars($displayName) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                            $statusColors = [
                                                'new' => 'primary',
                                                'contacted' => 'info', 
                                                'interested' => 'warning',
                                                'registered' => 'success',
                                                'not_interested' => 'secondary'
                                            ];
                                            $statusLabels = [
                                                'new' => 'Mới',
                                                'contacted' => 'Đã liên hệ',
                                                'interested' => 'Quan tâm', 
                                                'registered' => 'Đã đăng ký',
                                                'not_interested' => 'Không quan tâm'
                                            ];
                                            $statusColor = $statusColors[$customer['status']] ?? 'secondary';
                                            $statusLabel = $statusLabels[$customer['status']] ?? $customer['status'];
                                            ?>
                                            <span class="badge bg-<?= $statusColor ?>"><?= $statusLabel ?></span>
                                        </td>
                                        <td>
                                            <?php if ($customer['registration_status'] === 'registered'): ?>
                                                <i class="fas fa-check-circle text-success"></i>
                                                <span class="text-success">Đã đăng ký</span>
                                            <?php else: ?>
                                                <i class="fas fa-minus-circle text-muted"></i>
                                                <span class="text-muted">Chưa đăng ký</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($customer['payment_method'])): ?>
                                                <span class="badge bg-info"><?= htmlspecialchars($customer['payment_method']) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($customer['notes'])): ?>
                                                <span class="text-truncate d-inline-block" style="max-width: 150px;" 
                                                      title="<?= htmlspecialchars($customer['notes']) ?>">
                                                    <?= htmlspecialchars(substr($customer['notes'], 0, 30)) ?>
                                                    <?= strlen($customer['notes']) > 30 ? '...' : '' ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-users text-muted fa-3x mb-3"></i>
                        <h6 class="text-muted">Chưa có thông tin khách hàng</h6>
                        <p class="text-muted">Báo cáo này chưa có dữ liệu chi tiết về khách hàng</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-exclamation-triangle text-warning fa-4x mb-3"></i>
            <h5 class="text-muted">Không tìm thấy báo cáo</h5>
            <p class="text-muted mb-4">Báo cáo này không tồn tại hoặc đã bị xóa</p>
            <a href="/Quan_ly_trung_tam/public/reports" class="btn btn-primary">
                <i class="fas fa-arrow-left me-2"></i>Quay lại danh sách
            </a>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();

// Custom JavaScript for admin actions
$customJs = '';
if (isset($userRole) && $userRole === 'admin') {
    $customJs = '
    function deleteReport(reportId) {
        if (confirm("Bạn có chắc chắn muốn xóa báo cáo này? Hành động này không thể hoàn tác!")) {
            // Create form and submit
            const form = document.createElement("form");
            form.method = "POST";
            form.action = "/Quan_ly_trung_tam/public/reports/" + reportId + "/delete";
            
            // Add hidden method field for better compatibility
            const methodField = document.createElement("input");
            methodField.type = "hidden";
            methodField.name = "_method";
            methodField.value = "DELETE";
            
            form.appendChild(methodField);
            document.body.appendChild(form);
            form.submit();
        }
    }
    ';
}

// Render layout
useModernLayout('Chi tiết báo cáo', $content);
?>